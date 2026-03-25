<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    
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
            'customer_name'=> 'nullable|string'
        ]);

        try {
            // Utilitzem una Transaction per assegurar la integritat de les dades
            return DB::transaction(function () use ($request) {
                
                $isPreorder = $request->input('is_preorder', false);
                $pickupNumber = null;

                if ($isPreorder) {
                    $maxOrder = \App\Models\Order::whereDate('created_at', \Carbon\Carbon::today())->where('is_preorder', true)->max('pickup_number');
                    $pickupNumber = $maxOrder ? $maxOrder + 1 : 1;
                }

                // 2. Creem la capçalera de la comanda
                $order = Order::create([
                    'total_price'    => $request->total_price,
                    'payment_method' => $isPreorder ? 'Pendent' : ($request->payment_method ?? 'Efectiu'), 
                    'status'         => $isPreorder ? 'Pendent' : 'Pagat',
                    'worker_id'      => $request->worker_id,
                    'is_preorder'    => $isPreorder,
                    'pickup_number'  => $pickupNumber,
                    'pickup_time'    => $request->pickup_time,
                    'customer_name'  => $request->customer_name
                ]);

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

                // Retornem l'ID per poder confirmar al frontend que s'ha creat
                return response()->json([
                    'success'  => true,
                    'message'  => $isPreorder ? "Encàrrec #$pickupNumber guardat!" : 'Venda realitzada amb èxit!', 
                    'order_id' => $order->id,
                    'pickup_number' => $pickupNumber
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
        $orders = Order::with('items.product')
                       ->whereDate('created_at', \Carbon\Carbon::today())
                       ->where('is_preorder', true)
                       ->where('status', 'Pendent')
                       ->orderBy('pickup_time', 'asc')
                       ->get();
        return response()->json(['orders' => $orders]);
    }

    public function chargePreorder(Request $request, $id) {
        $request->validate([
            'payment_method' => 'required|string',
            'worker_id'      => 'required|exists:workers,id',
            'add_bag'        => 'nullable|boolean'
        ]);
        
        $order = Order::findOrFail($id);
        $order->payment_method = $request->payment_method;
        $order->worker_id = $request->worker_id; // Actulitzem al treballador que cobra
        
        if ($request->add_bag) {
            $order->total_price += 0.10;
        }

        $order->status = 'Pagat';
        $order->save();

        return response()->json(['success' => true]);
    }

    public function getOrderDetails($id) {
        $order = Order::with('items.product')->findOrFail($id);
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