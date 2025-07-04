<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\SocialController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Auth::routes();

// Маршруты для авторизации через соц. сети
Route::get('auth/{provider}', [SocialController::class, 'redirect'])->name('social.redirect');
Route::get('auth/{provider}/callback', [SocialController::class, 'callback'])->name('social.callback');

// Маршрут для тестирования улучшенного компонента выбора даты
Route::get('/templates/date-picker-demo', function() {
    return view('templates.date-picker-demo');
});


// Маршруты для всех типов пользователей
Route::get('/home', [HomeController::class, 'index'])->name('home');

// Маршрут для просмотра своих шаблонов (требует авторизации)
Route::middleware('auth')->get('/my-templates', [\App\Http\Controllers\UserTemplateController::class, 'index'])->name('my-templates');

// Публичный маршрут для просмотра всех шаблонов (доступен всем пользователям)
Route::get('/public-templates', [\App\Http\Controllers\UserTemplateController::class, 'publicTemplates'])->name('public-templates');

// Маршрут для создания шаблонов (оптимизированный)
Route::middleware('auth')->get('/templates/create', [\App\Http\Controllers\TemplateController::class, 'create'])->name('templates.create');

// Маршрут для удаления обложки из сессии
Route::middleware('auth')->post('/templates/remove-cover', [\App\Http\Controllers\TemplateController::class, 'removeCover'])->name('templates.remove-cover');

// Маршрут для предварительного просмотра шаблонов с оптимизированным кешированием
Route::get('/templates/preview/{template}', [\App\Http\Controllers\TemplateController::class, 'preview'])->name('templates.preview');

// Публичные маршруты для просмотра шаблонов (доступны всем)
Route::get('user-templates/{userTemplate}', [\App\Http\Controllers\UserTemplateController::class, 'show'])
     ->name('user-templates.show');
Route::get('user-templates/{userTemplate}/html', [\App\Http\Controllers\UserTemplateController::class, 'showHtml'])
    ->name('user-templates.show-html');

// Маршруты для пользовательских шаблонов (требуют авторизации)
Route::middleware('auth')->group(function () {
    Route::resource('user-templates', \App\Http\Controllers\UserTemplateController::class, [
        'names' => [
            'store' => 'user-templates.store',
            'destroy' => 'user-templates.destroy'
        ],
        'only' => ['store', 'destroy']
    ]);
    Route::get('user-templates/{userTemplate}/download', [\App\Http\Controllers\UserTemplateController::class, 'download'])
         ->name('user-templates.download');
    // Добавляем новый маршрут для сохранения шаблона в коллекцию
    Route::post('user-templates/{userTemplate}/save', [\App\Http\Controllers\UserTemplateController::class, 'saveToCollection'])
         ->name('user-templates.save');
});



// Маршрут для обновления CSRF-токена
Route::get('/refresh-csrf', function () {
    return response()->json(['token' => csrf_token()]);
})->name('refresh-csrf');

// Подключение групп маршрутов (исключая templates.php)
require __DIR__.'/web/auth.php';
require __DIR__.'/web/profile.php';
require __DIR__.'/web/media.php';
require __DIR__.'/web/sup.php';
require __DIR__.'/web/admin.php';
require __DIR__.'/web/client.php';
require __DIR__.'/web/templates.php';

if (app()->environment('production')) {
    URL::forceScheme('https');
}