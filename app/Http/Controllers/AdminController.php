<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Models\Worker;
use Carbon\Carbon;

class AdminController extends Controller
{
    // Mantenim el teu sistema de PIN si vols una doble capa, 
    // però Breeze ja protegeix la ruta amb el login de l'usuari.

    public function index() {
        // 1. Estadístiques d'avui
        $totalAvui = Order::whereDate('created_at', Carbon::today())->sum('total_price');
        $comandesComptador = Order::whereDate('created_at', Carbon::today())->count();
        
        // 2. Historial de vendes (amb relacions)
        $darreresVendes = Order::with(['worker', 'items.product'])
                            ->latest()
                            ->take(15)
                            ->get();

        // 3. Dades per a la gestió
        $productes = Product::with('categories')->orderBy('name')->get();
        $categories = Category::all();
        $treballadors = Worker::withCount('orders')->get();

        // 4. Millor treballador d'avui
        $millorWorker = Worker::withCount(['orders' => function($q) {
            $q->whereDate('created_at', Carbon::today());
        }])->orderBy('orders_count', 'desc')->first();

        return view('admin', compact(
            'totalAvui', 
            'comandesComptador', 
            'darreresVendes', 
            'productes', 
            'categories', 
            'treballadors',
            'millorWorker'
        ));
    }

    // --- GESTIÓ DE TREBALLADORS ---
    public function storeWorker(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'pin' => 'required|numeric|digits:4'
        ]);

        Worker::create($request->all());
        return back()->with('success', 'Treballador creat correctament');
    }

    public function deleteWorker($id) {
        Worker::findOrFail($id)->delete();
        return back()->with('success', 'Treballador eliminat');
    }

    // --- GESTIÓ DE PRODUCTES ---
    public function updateProduct(Request $request, $id) {
        $product = Product::findOrFail($id);
        $product->update($request->only(['name', 'price']));
        
        if ($request->has('category_id')) {
            $product->categories()->sync($request->category_id);
        }

        return back()->with('success', 'Producte actualitzat');
    }

    public function deleteProduct($id) {
        Product::findOrFail($id)->delete();
        return back()->with('success', 'Producte eliminat');
    }
}