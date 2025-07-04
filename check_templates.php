<?php

// Загружаем автозагрузчик Composer
require __DIR__.'/vendor/autoload.php';

// Загружаем приложение Laravel
$app = require_once __DIR__.'/bootstrap/app.php';

// Получаем экземпляр контейнера служб Laravel
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Получаем все шаблоны
$templates = \App\Models\Template::all();

echo "Всего шаблонов в базе данных: " . $templates->count() . "\n\n";

foreach ($templates as $template) {
    echo "ID: " . $template->id . "\n";
    echo "Название: " . $template->name . "\n";
    echo "Базовый шаблон: " . ($template->is_base_template ? 'Да' : 'Нет') . "\n";
    echo "Активен: " . ($template->is_active ? 'Да' : 'Нет') . "\n";
    echo "Дата создания: " . $template->created_at . "\n";
    echo "Дата обновления: " . $template->updated_at . "\n";
    echo "------------------------------------\n";
}

// Проверяем, соответствует ли содержимое базового шаблона в БД содержимому файла
$baseTemplate = \App\Models\Template::where('is_base_template', true)->first();

if ($baseTemplate) {
    $fileContent = file_get_contents(__DIR__ . '/templates/test-editable-template-original.php');
    
    if ($fileContent === $baseTemplate->html_content) {
        echo "Содержимое базового шаблона в БД совпадает с содержимым файла.\n";
    } else {
        echo "ВНИМАНИЕ: Содержимое базового шаблона в БД НЕ совпадает с содержимым файла!\n";
        echo "Возможно, необходимо обновить базовый шаблон в базе данных.\n";
    }
} else {
    echo "Базовый шаблон не найден в базе данных.\n";
}
