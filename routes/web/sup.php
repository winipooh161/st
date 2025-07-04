<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupController;

/*
|--------------------------------------------------------------------------
| SUP Routes
|--------------------------------------------------------------------------
*/

// Маршруты для SUP валюты
Route::middleware(['auth'])->prefix('sup')->name('sup.')->group(function () {
    Route::get('/', [SupController::class, 'index'])->name('index');
    Route::get('/transfer', [SupController::class, 'transfer'])->name('transfer');
    Route::post('/transfer', [SupController::class, 'executeTransfer'])->name('execute-transfer');
    Route::get('/balance', [SupController::class, 'getBalance'])->name('balance');
    
    // Маршруты для обработки пополнения SUP
    Route::post('/calculate', [SupController::class, 'calculateSup'])->name('calculate');
    Route::post('/payment', [SupController::class, 'processPayment'])->name('payment');
    Route::get('/progression', [SupController::class, 'getProgressionTable'])->name('progression');
    
    // Административные маршруты для SUP
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', [SupController::class, 'admin'])->name('admin');
        Route::post('/admin/add', [SupController::class, 'adminAdd'])->name('admin.add');
        Route::post('/admin/subtract', [SupController::class, 'adminSubtract'])->name('admin.subtract');
    });
});
