<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Worker;
use App\Support\ParkedTicketsSync;

// --- PÀGINA PRINCIPAL (TPV) ---
Route::get('/', function () {
    $categories = Category::all();

    // Comptem vendes per producte per ordenar per fama
    $salesCount = OrderItem::select('product_id', DB::raw('SUM(quantity) as total'))
        ->whereHas('order', fn($q) => $q->where('status', 'Pagat'))
        ->groupBy('product_id')
        ->pluck('total', 'product_id');

    // Productes actius, ordenats per fama (per a la categoria "Tots")
    $products = Product::with('categories')
        ->where('active', true)
        ->get()
        ->sortByDesc(fn($p) => $salesCount->get($p->id, 0))
        ->values();

    // Fama numèrica per a data-attribute al HTML
    $products = $products->map(function ($p) use ($salesCount) {
        $p->sales_count = $salesCount->get($p->id, 0);
        return $p;
    });

    // Top 5 productes del dia actual (per destacar-los al TPV)
    // DAYOFWEEK() a MySQL: 1=Diumenge, 2=Dilluns, ..., 7=Dissabte
    $diaActual = (int) now()->format('w'); // 0=Diumenge (PHP/Carbon)
    $mysqlDow  = $diaActual + 1;           // convertim a MySQL format
    $topAvui = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_venuts'))
        ->whereHas('order', function ($q) use ($mysqlDow) {
            $q->where('status', 'Pagat')
              ->whereRaw('DAYOFWEEK(created_at) = ?', [$mysqlDow]);
        })
        ->groupBy('product_id')
        ->orderByDesc('total_venuts')
        ->take(5)
        ->pluck('total_venuts', 'product_id');

    // Treballadors actius
    $workers = Worker::where('active', true)->get();

    $parkedStorageSyncToken = ParkedTicketsSync::token();

    return view('tpv.index', compact('categories', 'products', 'workers', 'topAvui', 'parkedStorageSyncToken'));
})->middleware(['auth']);


// Redirecció del dashboard de Breeze cap al nostre Admin
Route::get('/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');

// --- RUTES PROTEGIDES (Auth) ---
Route::middleware('auth')->group(function () {

    // Ruta per guardar vendes (Accessible per a tots els loguejats)


    // --- LC-3: PROTECCIÓ D'ACCÉS ADMIN PER ROL ---
    Route::group([
        'middleware' => function ($request, $next) {
            $user = Auth::user();

            if (!$user) {
                // Si és una petició AJAX (fetch), retornem JSON en lloc de redirecció
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'No autoritzat'], 403);
                }
                return redirect('/')->with('error', 'Accés denegat: cal iniciar sessió.');
            }

            // Validació extra: Només podem entrar si el PIN ha estat verificat
            if (!session('admin_pin_verified') && $request->path() !== 'admin/verify-pin') {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Falta PIN'], 403);
                }
                return redirect('/')->with('error', 'Introdueix el teu PIN d\'encarregat al TPV per accedir.');
            }

            return $next($request);
        }
    ], function () {

        // Panell d'Administració Principal
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

        // ... la resta de les teves rutes d'admin (verify-pin, categories, products, workers, etc.)
        Route::post('/admin/verify-pin', [AdminController::class, 'verifyPin'])->name('admin.verify-pin');
        Route::post('/admin/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::delete('/admin/categories/{id}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');
        Route::post('/admin/products', [AdminController::class, 'storeProduct'])->name('products.store');
        Route::put('/admin/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::delete('/admin/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.destroy');
        Route::post('/admin/workers', [AdminController::class, 'storeWorker'])->name('workers.store');
        Route::delete('/admin/workers/{id}', [AdminController::class, 'deleteWorker'])->name('workers.destroy');
        Route::put('/admin/workers/{id}', [AdminController::class, 'updateWorker'])->name('workers.update');
        Route::patch('/admin/workers/{id}/pin', [AdminController::class, 'updatePin'])->name('workers.updatePin');
        Route::delete('/admin/orders/{id}', [AdminController::class, 'destroyOrder'])->name('orders.destroy');
    });

    Route::middleware(['auth'])->group(function () {
        // Rutes d'encàrrecs
        Route::get('/orders/pending', [OrderController::class, 'getPendingPreorders'])->name('orders.pending');
        Route::post('/orders/{id}/charge', [OrderController::class, 'chargePreorder'])->name('orders.charge');
        Route::get('/orders/{id}/details', [OrderController::class, 'getOrderDetails'])->name('orders.details');
        Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelPreorder'])->name('orders.cancel');
    });

    // --- PERFIL D'USUARI (Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';