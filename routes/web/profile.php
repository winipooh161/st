<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

// Маршруты для профиля
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    // Маршрут для обновления профиля через AJAX
    Route::post('/user/update-profile', [ProfileController::class, 'update'])->name('user.update-profile');
    Route::post('/user/update-avatar', [ProfileController::class, 'updateAvatar'])->name('user.update-avatar');
});
