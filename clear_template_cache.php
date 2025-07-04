<?php

// Загружаем автозагрузчик Composer
require __DIR__.'/vendor/autoload.php';

// Загружаем приложение Laravel
$app = require_once __DIR__.'/bootstrap/app.php';

// Получаем экземпляр контейнера служб Laravel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Примечание: Кеширование базового шаблона удалено, поэтому эта функция больше не нужна
// \App\Models\Template::clearBaseTemplateCache();

echo "Кеширование базового шаблона отключено!\n";

// Очищаем общий кеш приложения для большей надежности
\Illuminate\Support\Facades\Cache::flush();

echo "Весь кеш приложения очищен!\n";

// Очищаем скомпилированные виды
$viewPath = storage_path('framework/views');
$files = glob($viewPath . '/*');
foreach ($files as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

echo "Скомпилированные представления удалены!\n";

echo "Операция успешно завершена. Теперь вы увидите последнюю версию шаблона на странице создания.\n";
