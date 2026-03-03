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
        ]);

        try {
            // Utilitzem una Transaction per assegurar la integritat de les dades
            return DB::transaction(function () use ($request) {
                
                // 2. Creem la capçalera de la comanda
                $order = Order::create([
                    'total_price'    => $request->total_price,
                    'payment_method' => $request->payment_method ?? 'Efectiu', 
                    'status'         => 'Pagat',
                    'worker_id'      => $request->worker_id,
                ]);

                // 3. Creem cada línia del tiquet (OrderItems)
                foreach ($request->cart as $item) {
                    OrderItem::create([
                        'order_id'      => $order->id,
                        'product_id'    => $item['id'],
                        'quantity'      => $item['quantity'],
                        'price_at_sale' => $item['price'], 
                    ]);
                }

                // Retornem l'ID per poder confirmar al frontend que s'ha creat
                return response()->json([
                    'success'  => true,
                    'message'  => 'Venda realitzada amb èxit!', 
                    'order_id' => $order->id
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
}