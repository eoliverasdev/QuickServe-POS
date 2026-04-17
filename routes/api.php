<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\OrderController;
use App\Models\Worker;
use Illuminate\Support\Facades\Route;

Route::get('/ping', function () {
    return response()->json([
        'ok' => true,
        'service' => 'quickserve-api',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/catalog', [CatalogController::class, 'index']);
    Route::get('/workers', function () {
        return response()->json([
            'workers' => Worker::query()
                ->where('active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    });
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/pending', [OrderController::class, 'getPendingPreorders']);
    Route::post('/orders/{id}/charge', [OrderController::class, 'chargePreorder']);
    Route::get('/orders/{id}/details', [OrderController::class, 'getOrderDetails']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancelPreorder']);

    Route::prefix('admin')->group(function () {
        Route::post('/verify-pin', [AdminController::class, 'verifyPin']);
        Route::get('/dashboard', [AdminController::class, 'dashboard']);

        Route::get('/categories', [AdminController::class, 'listCategories']);
        Route::post('/categories', [AdminController::class, 'storeCategory']);
        Route::put('/categories/{id}', [AdminController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [AdminController::class, 'destroyCategory']);

        Route::get('/products', [AdminController::class, 'listProducts']);
        Route::post('/products', [AdminController::class, 'storeProduct']);
        Route::post('/products/upload-image', [AdminController::class, 'uploadProductImage']);
        Route::put('/products/{id}', [AdminController::class, 'updateProduct']);
        Route::delete('/products/{id}', [AdminController::class, 'destroyProduct']);

        Route::get('/workers', [AdminController::class, 'listWorkers']);
        Route::post('/workers', [AdminController::class, 'storeWorker']);
        Route::put('/workers/{id}', [AdminController::class, 'updateWorker']);
        Route::delete('/workers/{id}', [AdminController::class, 'destroyWorker']);

        Route::get('/orders', [AdminController::class, 'listOrders']);
        Route::get('/orders/{id}', [AdminController::class, 'showOrder']);
        Route::delete('/orders/{id}', [AdminController::class, 'destroyOrder']);
    });
});
