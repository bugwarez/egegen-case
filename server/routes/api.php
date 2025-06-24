<?php

use App\Http\Controllers\Api\NewsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
 * API Routes - Haber Yönetim Sistemi
 *
 * Bu dosya haberlerin API endpoint'lerini tanımlar
 * Bearer token middleware'ı ile korunmaktadır
 */

// Varsayılan kullanıcı route'u (Sanctum ile)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




// Haber API Route Grubu
Route::prefix('news')->name('api.news.')->group(function () {

    // Public route'lar (Bearer token gerektirmez)
    Route::get('/published', [NewsController::class, 'published'])->name('published');
    Route::get('/slug/{slug}', [NewsController::class, 'getBySlug'])->name('slug');

    // Korumalı route'lar (Bearer token gerekli)
    Route::middleware(['bearer.token'])->group(function () {
        // CRUD
        Route::get('/', [NewsController::class, 'index'])->name('index');
        Route::post('/', [NewsController::class, 'store'])->name('store');
        Route::get('/{news}', [NewsController::class, 'show'])->name('show');
        Route::put('/{news}', [NewsController::class, 'update'])->name('update');
        Route::patch('/{news}', [NewsController::class, 'update'])->name('patch');
        Route::delete('/{news}', [NewsController::class, 'destroy'])->name('destroy');
        Route::patch('/{news}/status', [NewsController::class, 'changeStatus'])->name('change-status');
    });
});

// API durumu kontrol endpointi
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => 'Egegen Haber API\'si çalışıyor.',
        'timestamp' => now()->format('Y-m-d H:i:s'),
        'version' => '1.0.0'
    ]);
})->name('api.health');

// Log görüntüleme endpoint'i (test için)
Route::get('/logs', function (Request $request) {
    $logs = \App\Models\Log::orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Son 10 istek logu',
        'data' => $logs,
        'total_logs' => \App\Models\Log::count()
    ]);
})->middleware(['bearer.token'])->name('api.logs');
