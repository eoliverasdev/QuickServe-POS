<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Worker;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // 1. Estadístiques d'avui
        $totalAvui = Order::whereDate('created_at', Carbon::today())->where('status', 'Pagat')->sum('total_price');
        $comandesComptador = Order::whereDate('created_at', Carbon::today())->where('status', 'Pagat')->count();

        // 1.1 Tancament de Caixa i Desglossament
        $efectiuAvui = Order::whereDate('created_at', Carbon::today())->where('payment_method', 'Efectiu')->sum('total_price');
        $targetaAvui = Order::whereDate('created_at', Carbon::today())->where('payment_method', 'Targeta')->sum('total_price');

        $ivaPercentatge = 21;
        $baseImposable = $totalAvui / (1 + ($ivaPercentatge / 100));
        $quotaIva = $totalAvui - $baseImposable;

        // 1.2 Tiquet Mig d'avui
        $tiquetMig = $comandesComptador > 0 ? round($totalAvui / $comandesComptador, 2) : 0;

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
        $millorWorker = Worker::withCount([
            'orders' => function ($q) {
                $q->whereDate('created_at', Carbon::today())->where('status', 'Pagat');
            }
        ])->orderBy('orders_count', 'desc')->first();

        // =============================================
        // 5. TOP PRODUCTES MÉS VENUTS (global)
        // =============================================
        $topProductes = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_venuts'))
            ->with('product')
            ->whereHas('order', function ($q) {
                $q->where('status', 'Pagat');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_venuts')
            ->take(10)
            ->get();

        // =============================================
        // 6. TOP PRODUCTES PER DIA DE LA SETMANA
        // =============================================
        // Noms dels dies en català (0=Diumenge, 1=Dilluns, ... 6=Dissabte)
        $diesCatala = [
            0 => 'Diumenge', 1 => 'Dilluns', 2 => 'Dimarts', 3 => 'Dimecres', 4 => 'Dijous', 5 => 'Divendres', 6 => 'Dissabte',
        ];

        // Abreviacions per les pestanyes (evitar Dim repetit per Dimarts/Dimecres)
        $diesCurtCatala = [
            0 => 'Diu', 1 => 'Dil', 2 => 'Dim', 3 => 'Dmc', 4 => 'Dij', 5 => 'Div', 6 => 'Dis',
        ];

        // A MySQL, DAYOFWEEK() retorna 1=Diumenge, 2=Dilluns, ... 7=Dissabte
        // El nostre mapa usa 0=Diumenge, per tant: MySQL_DOW = nostreDOW + 1
        $topPerDia = [];
        for ($dow = 0; $dow <= 6; $dow++) {
            $mysqlDow = $dow + 1; // 1=Diumenge, 2=Dilluns, ...
            $topPerDia[$dow] = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_venuts'))
                ->with('product')
                ->whereHas('order', function ($q) use ($mysqlDow) {
                    $q->where('status', 'Pagat')
                        ->whereRaw('DAYOFWEEK(created_at) = ?', [$mysqlDow]);
                })
                ->groupBy('product_id')
                ->orderByDesc('total_venuts')
                ->take(5)
                ->get();
        }

        // =============================================
        // 7. INGRESSOS PER DIA (últims 7 dies)
        // =============================================
        $diesCurtCatala = ['Diu', 'Dil', 'Dim', 'Dmc', 'Dij', 'Div', 'Dis'];
        $ingressosSetmana = [];
        $labelsSetmana = [];
        for ($i = 6; $i >= 0; $i--) {
            $dia = Carbon::today()->subDays($i);
            $total = Order::whereDate('created_at', $dia)->where('status', 'Pagat')->sum('total_price');
            $ingressosSetmana[] = round($total, 2);
            $labelsSetmana[] = $diesCurtCatala[$dia->dayOfWeek] . ' ' . $dia->format('d/m');
        }

        // =============================================
        // 8. HORES PUNTA (distribució de vendes per hora)
        // =============================================
        $vestesPerhora = Order::select(DB::raw('HOUR(created_at) as hora'), DB::raw('COUNT(*) as total'))
            ->where('status', 'Pagat')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
            ->groupBy('hora')
            ->orderBy('hora')
            ->get()
            ->mapWithKeys(fn($item) => [(int) $item->hora => $item->total]);

        // =============================================
        // 9. INGRESSOS TOTALS (últims 30 dies)
        // =============================================
        $totalMes = Order::where('status', 'Pagat')
            ->whereDate('created_at', '>=', Carbon::today()->subDays(30))
            ->sum('total_price');

        // =============================================
        // 10. RECOMPTE DE VENDES PER PRODUCTE per saber l'ordre de fama
        //     - Retornem com a mapa id => total_venuts per al TPV
        // =============================================
        $famaProductes = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_venuts'))
            ->whereHas('order', function ($q) {
                $q->where('status', 'Pagat');
            })
            ->groupBy('product_id')
            ->orderByDesc('total_venuts')
            ->get()
            ->mapWithKeys(fn($item) => [$item->product_id => $item->total_venuts]);

        // Dia de la setmana actual (per destacar-lo a la vista)
        $diaActual = (int) Carbon::now()->format('w'); // 0=Diumenge

        return view('admin.admin', compact(
            'totalAvui',
            'comandesComptador',
            'efectiuAvui',
            'targetaAvui',
            'baseImposable',
            'quotaIva',
            'ivaPercentatge',
            'tiquetMig',
            'darreresVendes',
            'productes',
            'categories',
            'treballadors',
            'millorWorker',
            'topProductes',
            'topPerDia',
            'diesCatala',
            'diesCurtCatala',
            'diaActual',
            'ingressosSetmana',
            'labelsSetmana',
            'vestesPerhora',
            'totalMes',
            'famaProductes'
        ));
    }

    // --- LC-1: GESTIÓ DE CATEGORIES ---
    public function storeCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:categories|max:255'
        ]);

        Category::create($request->all());
        // Redirigim a l'àncora #categories
        return back()->with('success', 'Categoria creada correctament!')->withFragment('categories-list');
    }

    public function destroyCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();
        // Redirigim a l'àncora #categories
        return back()->with('success', 'Categoria eliminada')->withFragment('categories-list');
    }

    // --- LC-2: GESTIÓ DE PRODUCTES ---
    public function storeProduct(Request $request)
    {
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

    public function updateProduct(Request $request, $id)
    {
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

    public function deleteProduct($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return back()->with('success', 'Producte eliminat')->withFragment('productes-list');
    }

    // --- GESTIÓ DE TREBALLADORS ---
    public function storeWorker(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'pin' => 'nullable|numeric|digits:4'
        ]);

        Worker::create($request->all());
        // Redirigim a l'àncora #treballadors
        return back()->with('success', 'Treballador creat correctament')->withFragment('treballadors-list');
    }

    public function deleteWorker($id)
    {
        Worker::findOrFail($id)->delete();
        // Redirigim a l'àncora #treballadors
        return back()->with('success', 'Treballador eliminat')->withFragment('treballadors-list');
    }

    // --- GESTIÓ DE COMANDES ---
    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        // Redirigim a l'àncora #comandes
        return back()->with('success', 'Venda anul·lada correctament')->withFragment('comandes');
    }
}