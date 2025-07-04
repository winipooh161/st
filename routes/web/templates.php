<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TemplateQrController;

/*
|--------------------------------------------------------------------------
| Template Routes
|--------------------------------------------------------------------------
|
| Маршруты для работы с шаблонами
|
*/

// Публичные маршруты для шаблонов
Route::group(['prefix' => 'templates'], function () {
    // Демонстрация редактора шаблонов
    Route::get('/demo', function () {
        return view('templates.demo-template');
    })->name('templates.demo');
    
    // Создание нового шаблона
    Route::get('/create', [TemplateController::class, 'create'])
        ->name('templates.create');
        
    // Просмотр шаблона
    Route::get('/{template}', [TemplateController::class, 'show'])
        ->name('templates.show');
        
    // QR-код маршруты вынесены из префикса templates
});

// QR-код маршруты (публичные, без префикса templates)
Route::group(['prefix' => 'qr'], function () {
    // Получение шаблона по QR-коду (публичный маршрут)
    Route::get('/claim/{token}', [TemplateQrController::class, 'claimViaQr'])
        ->name('template.claim-via-qr');
        
    // Деактивация шаблона по QR-коду (публичный маршрут)  
    Route::get('/deactivate/{token}', [TemplateQrController::class, 'deactivateViaQr'])
        ->name('template.deactivate-via-qr');
});

// Защищенные маршруты для QR-кодов
Route::middleware('auth')->group(function () {
    Route::group(['prefix' => 'template-qr'], function () {
        // Проверка статуса серии шаблонов
        Route::get('/{template}/series-status', [TemplateQrController::class, 'checkSeriesStatus'])
            ->name('template-qr.series-status');
    
        // Генерация QR-кода для клиента
        Route::post('/{template}/generate-client', [TemplateQrController::class, 'generateClientQr'])
            ->name('template-qr.generate-client');
            
        // Генерация QR-кода для создателя
        Route::post('/{template}/generate-creator', [TemplateQrController::class, 'generateCreatorQr'])
            ->name('template-qr.generate-creator');
            
        // Получение статуса QR-кодов
        Route::get('/{template}/status', [TemplateQrController::class, 'getQrStatus'])
            ->name('template-qr.status');
    });
});

// API для работы с шаблонами
Route::group(['prefix' => 'api/template-fields'], function () {
    // Обновление полей шаблона
    Route::post('/update', [TemplateController::class, 'updateFields'])
        ->name('api.template-fields.update');
});
