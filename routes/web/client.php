<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateEditorController;
use App\Http\Controllers\UserTemplateController;
use App\Http\Controllers\AcquiredTemplateController;
use App\Http\Controllers\FormSubmissionController;
use App\Http\Controllers\ClientController;

/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
*/

// Маршруты для клиентов (и администраторов)
Route::prefix('client')->middleware('role:client,admin')->group(function () {
    Route::get('/', [ClientController::class, 'index'])->name('client.dashboard');
    
 
    // Маршруты для управления папками полученных шаблонов удалены
});
