<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MediaEditorController;

/*
|--------------------------------------------------------------------------
| Media Routes
|--------------------------------------------------------------------------
*/

// Маршруты для медиа-редактора
Route::middleware(['auth', 'role:client,admin'])->group(function () {
    // Основной медиа-редактор
    Route::get('/media/editor', [MediaEditorController::class, 'index'])->name('media.editor');
    Route::get('/media/editor/{template_id}', [MediaEditorController::class, 'editForTemplate'])->name('media.editor.template');
    Route::post('/media/process', [MediaEditorController::class, 'processMedia'])->name('media.process');
    Route::post('/media/process-and-create', [MediaEditorController::class, 'processMediaAndCreateTemplate'])->name('media.process.create');
    
    // Дополнительные маршруты для медиа
    Route::get('/media/{template?}', [MediaController::class, 'editor'])->name('media.show');
    Route::get('/media/template/{template}', [MediaController::class, 'editorForTemplate'])->name('media.template');
    Route::post('/media/upload', [MediaController::class, 'process'])->name('media.upload');
    
    // Маршрут для создания шаблона
    Route::get('/create-template', function() {
        return redirect()->route('media.editor');
    })->name('create.template');
});
