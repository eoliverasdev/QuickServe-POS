<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Product;
use App\Models\Worker;

// --- PÀGINA PRINCIPAL (TPV) ---
Route::get('/', function () {
    $categories = Category::all();
    $products = Product::with('categories')->get();
    $workers = Worker::all();

    return view('tpv.index', compact('categories', 'products', 'workers'));
})->middleware(['auth']);


// Redirecció del dashboard de Breeze cap al nostre Admin
Route::get('/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// --- RUTES PROTEGIDES (Auth) ---
Route::middleware('auth')->group(function () {
    
    // --- LC-3: PROTECCIÓ D'ACCÉS ADMIN PER ROL ---
    // Fem servir Route::group per evitar l'error "Closure could not be converted to string"
    Route::group(['middleware' => function ($request, $next) {
        $user = Auth::user();

        // Verificació per ROL en lloc d'email (Molt més optim)
        if (!$user || $user->role !== 'admin') {
            return redirect('/')->with('error', 'Accés denegat: es requereixen permisos d\'administrador.');
        }
        
        return $next($request);
    }], function () {

        // Panell d'Administració Principal
        Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

        // --- LC-4: VERIFICACIÓ DE PIN ---
        Route::post('/admin/verify-pin', [AdminController::class, 'verifyPin'])->name('admin.verify-pin');

        // --- LC-1: GESTIÓ DE CATEGORIES ---
        Route::post('/admin/categories', [AdminController::class, 'storeCategory'])->name('categories.store');
        Route::delete('/admin/categories/{id}', [AdminController::class, 'destroyCategory'])->name('categories.destroy');

        // --- LC-2: GESTIÓ DE PRODUCTES ---
        Route::post('/admin/products', [AdminController::class, 'storeProduct'])->name('products.store');
        Route::put('/admin/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
        Route::delete('/admin/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.destroy');

        // --- GESTIÓ DE TREBALLADORS ---
        Route::post('/admin/workers', [AdminController::class, 'storeWorker'])->name('workers.store');
        Route::delete('/admin/workers/{id}', [AdminController::class, 'deleteWorker'])->name('workers.destroy');
        Route::patch('/admin/workers/{id}/pin', [AdminController::class, 'updatePin'])->name('workers.updatePin');

        // --- HISTORIAL I COMANDES ---
        Route::delete('/admin/orders/{id}', [AdminController::class, 'deleteOrder'])->name('orders.destroy');
    });

    // --- PERFIL D'USUARI (Breeze) ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';