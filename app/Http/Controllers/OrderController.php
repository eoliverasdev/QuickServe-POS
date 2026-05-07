<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\FiscalInvoiceNumberAssigner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
    public function __construct(
        protected FiscalInvoiceNumberAssigner $fiscalInvoiceNumberAssigner
    ) {}

    public function store(Request $request) 
    {
        // 1. Validació estricta
        $request->validate([
            'worker_id'   => 'required|exists:workers,id',
            'total_price' => 'required|numeric',
            'cart'        => 'required|array',
            'cart.*.id'   => 'required|exists:products,id',
            'is_preorder' => 'nullable|boolean',
            'pickup_time' => 'nullable|string',
            'customer_name'=> 'nullable|string',
            'pickup_number' => 'nullable|integer|min:1',
            'pickup_date' => 'nullable|date_format:Y-m-d',
        ]);

        try {
            $hasPickupDateColumn = Schema::hasColumn('orders', 'pickup_date');
            // Utilitzem una Transaction per assegurar la integritat de les dades
            return DB::transaction(function () use ($request, $hasPickupDateColumn) {
                
                $isPreorder = $request->input('is_preorder', false);
                $pickupNumber = null;
                $pickupDate = null;

                if ($isPreorder) {
                    // Si no hi ha columna pickup_date (entorn sense migrar),
                    // mantenim la lògica antiga per evitar 500.
                    $pickupDate = $request->input('pickup_date')
                        ?: \Carbon\Carbon::today()->toDateString();
                    $requestedPickup = $request->input('pickup_number');
                    if ($requestedPickup !== null) {
                        $alreadyUsedQuery = \App\Models\Order::where('is_preorder', true)
                            ->where('pickup_number', $requestedPickup);
                        if ($hasPickupDateColumn) {
                            $alreadyUsedQuery->where('pickup_date', $pickupDate);
                        } else {
                            $alreadyUsedQuery->whereDate('created_at', \Carbon\Carbon::today());
                        }
                        $alreadyUsed = $alreadyUsedQuery->exists();
                        if (! $alreadyUsed) {
                            $pickupNumber = (int) $requestedPickup;
                        }
                    }

                    if ($pickupNumber === null) {
                        $maxQuery = \App\Models\Order::where('is_preorder', true);
                        if ($hasPickupDateColumn) {
                            $maxQuery->where('pickup_date', $pickupDate);
                        } else {
                            $maxQuery->whereDate('created_at', \Carbon\Carbon::today());
                        }
                        $maxOrder = $maxQuery->max('pickup_number');
                        $pickupNumber = $maxOrder ? $maxOrder + 1 : 1;
                    }
                }

                // 2. Creem la capçalera de la comanda
                // IMPORTANT: pickup_date només s'envia en encàrrecs. Això
                // manté compatibilitat amb entorns on encara no s'ha executat
                // la migració que afegeix aquesta columna.
                $orderData = [
                    'total_price'    => $request->total_price,
                    'payment_method' => $isPreorder ? 'Pendent' : ($request->payment_method ?? 'Efectiu'), 
                    'status'         => $isPreorder ? 'Pendent' : 'Pagat',
                    'worker_id'      => $request->worker_id,
                    'is_preorder'    => $isPreorder,
                    'pickup_number'  => $pickupNumber,
                    'pickup_time'    => $request->pickup_time,
                    'customer_name'  => $request->customer_name,
                ];
                if ($isPreorder && $hasPickupDateColumn) {
                    $orderData['pickup_date'] = $pickupDate;
                }
                $order = Order::create($orderData);

                // 3. Creem cada línia del tiquet (OrderItems) i regulem lestoc
                foreach ($request->cart as $item) {
                    OrderItem::create([
                        'order_id'      => $order->id,
                        'product_id'    => $item['id'],
                        'quantity'      => $item['quantity'],
                        'price_at_sale' => $item['price'],
                        'notes'         => $item['notes'] ?? null,
                    ]);

                    // Llegim el nom
                    // Si és "1/2 Pollastre (Pit i cuixa)", descomptem 0.5 de "Pollastre"
                    if ($item['name'] === '1/2 Pollastre (Pit i cuixa)') {
                        \App\Models\Product::where('name', 'Pollastre')
                            ->whereNotNull('stock')
                            ->decrement('stock', $item['quantity'] * 0.5);
                    } else {
                        \App\Models\Product::where('id', $item['id'])
                            ->whereNotNull('stock')
                            ->decrement('stock', $item['quantity']);
                    }
                }

                if (! $isPreorder) {
                    $this->fiscalInvoiceNumberAssigner->assignIfMissing($order);
                }

                // Retornem l'ID per poder confirmar al frontend que s'ha creat
                return response()->json([
                    'success'  => true,
                    'message'  => $isPreorder ? "Encàrrec #$pickupNumber guardat!" : 'Venda realitzada amb èxit!', 
                    'order_id' => $order->id,
                    'pickup_number' => $pickupNumber,
                    'fiscal_full_number' => $order->fresh()->fiscal_full_number,
                ], 201);
            });

        } catch (\Exception $e) {
            // Si hi ha qualsevol error (BD, codi, etc.), es fa Rollback automàtic
            Log::error("Error en la venda TPV: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error intern al registrar la venda.',
                'error'   => $e->getMessage() 
            ], 500);
        }
    }

    public function getPendingPreorders() {
        $query = Order::with('items.product')
            ->where('is_preorder', true)
            ->where('status', 'Pendent');

        if (Schema::hasColumn('orders', 'pickup_date')) {
            // Encàrrecs multi-dia: ordenem per dia de recollida i hora.
            $query->orderByRaw('COALESCE(pickup_date, DATE(created_at)) ASC');
        } else {
            // Compatibilitat amb entorns sense migrar.
            $query->whereDate('created_at', \Carbon\Carbon::today());
        }

        $orders = $query
            ->orderBy('pickup_time', 'asc')
            ->get();
        return response()->json(['orders' => $orders]);
    }

    public function chargePreorder(Request $request, $id) {
        $request->validate([
            'payment_method' => 'required|string',
            'worker_id'      => 'required|exists:workers,id',
            'bag_count'      => 'nullable|integer|min:0|max:99',
            'bag_product_id' => 'nullable|exists:products,id',
        ]);

        return DB::transaction(function () use ($request, $id) {
            $order = Order::query()->whereKey($id)->lockForUpdate()->firstOrFail();
            $order->payment_method = $request->payment_method;
            $order->worker_id = $request->worker_id;

            $bagCount = max(0, min(99, (int) $request->input('bag_count', 0)));
            if ($bagCount > 0) {
                $bagProductId = (int) $request->input('bag_product_id', 0);
                $bagProduct = null;

                if ($bagProductId > 0) {
                    $bagProduct = Product::query()->find($bagProductId);
                }

                if (! $bagProduct) {
                    $bagProduct = Product::query()
                        ->where('name', 'Bossa')
                        ->orWhere('name', 'Bolsa')
                        ->first();
                }

                if (! $bagProduct) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No s\'ha trobat el producte de bossa.',
                    ], 422);
                }

                $bagUnitPrice = (float) $bagProduct->price;
                $order->total_price = round((float) $order->total_price + ($bagCount * $bagUnitPrice), 2);

                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $bagProduct->id,
                    'quantity'      => $bagCount,
                    'price_at_sale' => $bagUnitPrice,
                    'notes'         => null,
                ]);
            }

            $order->status = 'Pagat';
            $order->save();

            $this->fiscalInvoiceNumberAssigner->assignIfMissing($order);

            return response()->json([
                'success' => true,
                'fiscal_full_number' => $order->fresh()->fiscal_full_number,
            ]);
        });
    }

    public function getOrderDetails($id) {
        $order = Order::with(['items.product', 'worker'])->findOrFail($id);
        return response()->json(['order' => $order]);
    }

    public function cancelPreorder($id) {
        $order = Order::with('items')->findOrFail($id);
        
        // Restore stock
        foreach ($order->items as $item) {
            $product = \App\Models\Product::find($item->product_id);
            if ($product && $product->stock !== null) {
                if ($product->name === '1/2 Pollastre (Pit i cuixa)') {
                    $pollastre = \App\Models\Product::where('name', 'Pollastre')->first();
                    if ($pollastre) {
                        $pollastre->stock += ($item->quantity * 0.5);
                        $pollastre->save();
                    }
                } else {
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
        }
        
        $order->delete();
        return response()->json(['success' => true]);
    }
}