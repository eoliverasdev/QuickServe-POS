<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Pàgina principal (TPV)
Route::get('/', function () {
    // Aquí pots carregar la vista del teu TPV directament
    return view('index'); 
});


// Canviem la funció per una crida al mètode 'index' del controlador
Route::get('/dashboard', [AdminController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('admin');

// --- RUTES PROTEGIDES (Només usuaris loguejats amb Breeze) ---
Route::middleware('auth')->group(function () {
    
    // Panell d'Administració Principal
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');

    // --- GESTIÓ DE TREBALLADORS ---
    // Crear treballador
    Route::post('/admin/workers', [AdminController::class, 'storeWorker'])->name('workers.store');
    // Esborrar treballador
    Route::delete('/admin/workers/{id}', [AdminController::class, 'deleteWorker'])->name('workers.destroy');
    // Actualitzar PIN (opcionalment pots fer una ruta específica)
    Route::patch('/admin/workers/{id}/pin', [AdminController::class, 'updatePin'])->name('workers.updatePin');

    // --- GESTIÓ DE PRODUCTES I CATEGORIES ---
    // Editar producte (Preu/Nom)
    Route::put('/admin/products/{id}', [AdminController::class, 'updateProduct'])->name('products.update');
    // Esborrar producte
    Route::delete('/admin/products/{id}', [AdminController::class, 'deleteProduct'])->name('products.destroy');

    // Rutes del perfil de Breeze
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';