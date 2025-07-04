<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\TemplateCategoryController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

// Маршруты для администраторов
Route::prefix('admin')->middleware('role:admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Маршруты для управления пользователями
    Route::resource('users', UserController::class, ['as' => 'admin']);
    
    // Маршруты для управления шаблонами
    Route::resource('templates', TemplateController::class, ['as' => 'admin']);
    Route::patch('templates/{template}/toggle-base', [TemplateController::class, 'toggleBaseTemplate'])
         ->name('admin.templates.toggle-base');
    Route::post('templates/{template}/analyze', [TemplateController::class, 'analyzeEditableElements'])
         ->name('admin.templates.analyze');
  
});
