<?php

// Подключаемся через Laravel для обновления шаблона
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Создание/обновление базового шаблона...\n";

try {
    // Получаем текущий базовый шаблон напрямую через модель
    $baseTemplate = \App\Models\Template::where('is_base_template', true)->first();
    
    // Если базовый шаблон не найден, создаем новый
    if (!$baseTemplate) {
        $baseTemplate = new \App\Models\Template();
        $baseTemplate->name = 'Базовый шаблон';
        $baseTemplate->description = 'Стандартный редактируемый шаблон';
        $baseTemplate->is_base_template = true;
        $baseTemplate->is_active = true;
        echo "Создаем новый базовый шаблон...\n";
    } else {
        echo "Базовый шаблон найден: " . $baseTemplate->name . " (ID: " . $baseTemplate->id . ")\n";
    }
    
    echo "Базовый шаблон: " . $baseTemplate->name . " (ID: " . $baseTemplate->id . ")\n";
    
    // Получаем содержимое шаблона из файла test-editable-template.php
    $templatePath = __DIR__ . '/templates/test-editable-template.php';
    $testHtml = file_get_contents($templatePath);
    
    if (!$testHtml) {
        throw new \Exception("Не удалось прочитать файл шаблона: {$templatePath}");
    }
    
    echo "Получено содержимое шаблона из файла: {$templatePath}\n";

    // Обновляем HTML содержимое
    $baseTemplate->html_content = $testHtml;
    
    // Определяем редактируемые элементы
    $baseTemplate->editable_elements = [
        'company_name' => ['type' => 'text', 'value' => 'ООО "Ваша Компания"'],
        'start_date' => ['type' => 'date', 'value' => '01.01.2025'],
        'end_date' => ['type' => 'date', 'value' => '31.12.2025'],
        'summary' => ['type' => 'text', 'value' => 'Здесь представлен пример редактируемого текста в шаблоне документа.'],
        'main_content' => ['type' => 'text', 'value' => 'Данный шаблон демонстрирует возможности прямого редактирования текста в документе.'],
        'additional_content' => ['type' => 'text', 'value' => 'Все изменения автоматически сохраняются и синхронизируются с сервером.'],
        'phone' => ['type' => 'text', 'value' => '+7 (999) 123-45-67'],
        'email' => ['type' => 'text', 'value' => 'example@example.com'],
        'update_date' => ['type' => 'date', 'value' => '04.07.2025']
    ];
    
    $baseTemplate->save();
    
    echo "Шаблон успешно обновлен!\n";
    
    // Очищаем кеш приложения
    \Illuminate\Support\Facades\Cache::flush();
    echo "Кеш приложения очищен!\n";
    
    // Очищаем общий кеш приложения
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
    
    echo "Готово! Теперь вы увидите последнюю версию шаблона на странице создания.\n";
    
} catch (\Exception $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}
