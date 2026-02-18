<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validació: Ens assegurem que el worker_id existeix
        $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'total_price' => 'required|numeric',
            'cart' => 'required|array'
        ]);

        // Utilitzem una Transaction per seguretat
        return DB::transaction(function () use ($request) {
            
            // 2. Creem la capçalera de la comanda
            $order = Order::create([
                'total_price'    => $request->total_price,
                'payment_method' => $request->payment_method, 
                'status'         => 'Pagat',
                'worker_id'      => $request->worker_id, // <--- CANVI: Abans era user_id
            ]);

            // 3. Creem cada línia del tiquet
            foreach ($request->cart as $item) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $item['id'],
                    'quantity'      => $item['quantity'],
                    'price_at_sale' => $item['price'], 
                ]);
            }

            return response()->json([
                'message' => 'Venda realitzada amb èxit!', 
                'order_id' => $order->id
            ]);
        });
    }
}