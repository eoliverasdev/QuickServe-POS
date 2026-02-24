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
        $totalAvui = Order::whereDate('created_at', Carbon::today())->sum('total_price');
        $comandesComptador = Order::whereDate('created_at', Carbon::today())->count();
        
        // 2. Historial de vendes (amb relacions per evitar el problema N+1)
        $darreresVendes = Order::with(['worker', 'items.product'])
                            ->latest()
                            ->take(15)
                            ->get();

        // 3. Dades per a la gestió
        $productes = Product::with('categories')->orderBy('name')->get();
        $categories = Category::all();
        $treballadors = Worker::withCount('orders')->get();

        // 4. Millor treballador d'avui (amb verificació)
        $millorWorker = Worker::withCount(['orders' => function($q) {
            $q->whereDate('created_at', Carbon::today());
        }])->orderBy('orders_count', 'desc')->first();

        return view('admin.admin', compact(
            'totalAvui', 
            'comandesComptador', 
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
        return redirect(url()->previous() . '#categories')->with('success', 'Categoria creada correctament!');
    }

    public function destroyCategory($id) {
        $category = Category::findOrFail($id);
        $category->delete();
        // Redirigim a l'àncora #categories
        return redirect(url()->previous() . '#categories')->with('success', 'Categoria eliminada');
    }

    // --- LC-2: GESTIÓ DE PRODUCTES ---
    public function storeProduct(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string'
        ]);

        $product = Product::create($request->only(['name', 'price', 'image']));
        $product->categories()->attach($request->category_id);

        // Redirigim a l'àncora #productes
        return redirect(url()->previous() . '#productes')->with('success', 'Producte creat amb la seva categoria!');
    }

    public function updateProduct(Request $request, $id) {
        $product = Product::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id'
        ]);

        $product->update($request->only(['name', 'price']));
        $product->categories()->sync([$request->category_id]);

        // Redirigim a l'àncora #productes
        return redirect(url()->previous() . '#productes')->with('success', 'Producte actualitzat');
    }

    public function deleteProduct($id) {
        Product::findOrFail($id)->delete();
        // Redirigim a l'àncora #productes
        return redirect(url()->previous() . '#productes')->with('success', 'Producte eliminat');
    }

    // --- GESTIÓ DE TREBALLADORS ---
    public function storeWorker(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'pin'  => 'nullable|numeric|digits:4'
        ]);

        Worker::create($request->all());
        // Redirigim a l'àncora #treballadors
        return redirect(url()->previous() . '#treballadors')->with('success', 'Treballador creat correctament');
    }

    public function deleteWorker($id) {
        Worker::findOrFail($id)->delete();
        // Redirigim a l'àncora #treballadors
        return redirect(url()->previous() . '#treballadors')->with('success', 'Treballador eliminat');
    }

    // --- GESTIÓ DE COMANDES ---
    public function deleteOrder($id) {
        $order = Order::findOrFail($id);
        $order->delete();
        // Redirigim a l'àncora #comandes
        return redirect(url()->previous() . '#comandes')->with('success', 'Venda anul·lada correctament');
    }
}