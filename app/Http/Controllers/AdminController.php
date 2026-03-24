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
    public function index() {
        // 1. Estadístiques d'avui
        $totalAvui = Order::whereDate('created_at', Carbon::today())->where('status', 'Pagat')->sum('total_price');
        $comandesComptador = Order::whereDate('created_at', Carbon::today())->where('status', 'Pagat')->count();
        
        // 1.1 Tancament de Caixa i Desglossament
        $efectiuAvui = Order::whereDate('created_at', Carbon::today())->where('payment_method', 'Efectiu')->sum('total_price');
        $targetaAvui = Order::whereDate('created_at', Carbon::today())->where('payment_method', 'Targeta')->sum('total_price');
        
        $ivaPercentatge = 21; // Segons frontend
        $baseImposable = $totalAvui / (1 + ($ivaPercentatge / 100));
        $quotaIva = $totalAvui - $baseImposable;

        // 2. Historial de vendes
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

        return view('admin.admin', compact(
            'totalAvui', 
            'comandesComptador', 
            'efectiuAvui',
            'targetaAvui',
            'baseImposable',
            'quotaIva',
            'ivaPercentatge',
            'darreresVendes', 
            'productes', 
            'categories', 
            'treballadors',
            'millorWorker'
        ));
    }

    // --- LC-1: GESTIÓ DE CATEGORIES ---
    public function storeCategory(Request $request) {
        $request->validate([
            'name' => 'required|string|unique:categories|max:255'
        ]);
        
        Category::create($request->all());
        // Redirigim a l'àncora #categories
        return back()->with('success', 'Categoria creada correctament!')->withFragment('categories-list');
    }

    public function destroyCategory($id) {
        $category = Category::findOrFail($id);
        $category->delete();
        // Redirigim a l'àncora #categories
        return back()->with('success', 'Categoria eliminada')->withFragment('categories-list');
    }

    // --- LC-2: GESTIÓ DE PRODUCTES ---
    public function storeProduct(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string',
            'active' => 'true'
        ]);

        $product = Product::create($request->only(['name', 'price', 'stock', 'image']));
        $product->categories()->attach($request->category_id);

        return back()->with('success', 'Producte creat amb la seva categoria!')->withFragment('productes-list');
    }

    public function updateProduct(Request $request, $id) {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'nullable|numeric|min:0',
            'category_id' => 'required|exists:categories,id'
        ]);

        $product->update($request->only(['name', 'price', 'stock']));
        $product->categories()->sync([$request->category_id]);

        // Redirigim a l'àncora #productes
        return back()->with('success', 'Producte actualitzat')->withFragment('productes-list');
    }

    public function deleteProduct($id) {
        $product = Product::findOrFail($id);
        $product->delete();

        return back()->with('success', 'Producte eliminat')->withFragment('productes-list');
    }

    // --- GESTIÓ DE TREBALLADORS ---
    public function storeWorker(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'pin'  => 'nullable|numeric|digits:4'
        ]);

        Worker::create($request->all());
        // Redirigim a l'àncora #treballadors
        return back()->with('success', 'Treballador creat correctament')->withFragment('treballadors-list');
    }

    public function deleteWorker($id) {
        Worker::findOrFail($id)->delete();
        // Redirigim a l'àncora #treballadors
        return back()->with('success', 'Treballador eliminat')->withFragment('treballadors-list');
    }

    // --- GESTIÓ DE COMANDES ---
    public function deleteOrder($id) {
        $order = Order::findOrFail($id);
        $order->delete();
        // Redirigim a l'àncora #comandes
        return back()->with('success', 'Venda anul·lada correctament')->withFragment('comandes');
    }
}