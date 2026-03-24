<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\OrderController; // <--- AFEGEIX AIXÒ
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Product;
use App\Models\Worker;

// --- PÀGINA PRINCIPAL (TPV) ---
Route::get('/', function () {
    $categories = Category::all();

    // Filtrem productes actius
    $products = Product::with('categories')->where('active', true)->get();

    // Filtrem també treballadors actius
    $workers = Worker::where('active', true)->get();

    return view('tpv.index', compact('categories', 'products', 'workers'));
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