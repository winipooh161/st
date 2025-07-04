<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TemplateStatusController;
use App\Http\Controllers\Api\QrVerificationController;
use App\Http\Controllers\Api\TemplateFieldsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




// Маршрут для обновления полей шаблона
Route::post('/templates/update-fields', [TemplateFieldsController::class, 'updateFields'])
    ->middleware('web', 'auth');

// Новый маршрут для обновления полей шаблона через прямое редактирование
Route::post('/template-fields/update', [TemplateFieldsController::class, 'updateFields'])
    ->middleware('web', 'auth');
