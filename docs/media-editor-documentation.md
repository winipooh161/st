# Медиа-редактор - Документация

## Обзор

Оптимизированный медиа-редактор для Laravel приложения, поддерживающий загрузку, редактирование и обработку изображений и видео файлов. Редактор оптимизирован для мобильных устройств и поддерживает сенсорные жесты.

## Основные возможности

### Для изображений:
- ✅ Drag & Drop загрузка
- ✅ Сенсорные жесты (перетаскивание, масштабирование)
- ✅ Поддержка мыши для десктопа
- ✅ Масштабирование и перемещение изображения
- ✅ Автоматическое кадрирование

### Для видео:
- ✅ Предпросмотр видео
- ✅ Обрезка по времени (до 15 секунд)
- ✅ Интуитивные ползунки для выбора диапазона

## Структура файлов

```
public/js/media-editor-optimized.js     # Оптимизированный JavaScript файл
resources/css/media-editor.css          # CSS стили
resources/views/media/editor.blade.php  # Главный шаблон
resources/views/components/media-editor/
├── header.blade.php                   # Шапка редактора 
└── loader.blade.php                   # Загрузчик стилей и скриптов
```

## Маршруты

```php
// GET /media/editor/{template_id?}
Route::get('/media/editor/{template_id?}', [MediaEditorController::class, 'index'])
    ->name('media.editor');

// POST /media/process
Route::post('/media/process', [MediaEditorController::class, 'processMedia'])
    ->name('media.process');
```

## API методы

### POST /media/process
Обрабатывает загруженные медиафайлы

**Параметры:**
- `media_file` (file) - медиафайл
- `template_id` (int) - ID шаблона
- `crop_data` (json) - данные кадрирования для изображений
- `video_start` (float) - время начала для видео
- `video_end` (float) - время окончания для видео

**Ответ:**
```json
{
    "success": true,
    "redirect_url": "/templates/editor/1",
    "file_path": "/storage/covers/user_1/processed_file.webp",
    "file_type": "image",
    "compression_data": {
        "original_size": 1024000,
        "new_size": 512000,
        "compression_ratio": 50
    }
}
```

## Использование в шаблонах

```php
// Подключение компонента медиа-редактора
@include('components.media-editor.loader')

// Шапка медиа-редактора с параметрами
@include('components.media-editor.header', [
    'title' => 'Загрузка медиафайлов',
    'description' => 'Выберите изображение или видео',
    'template' => $template
])
```

## JavaScript API

Основные методы и объекты доступны через переменные:

```javascript
// Состояние редактора
const state = {
    currentFile: null,     // Текущий файл
    fileType: null,        // Тип файла (image/video)
    templateId: null,      // ID шаблона
    scale: 1,              // Масштаб изображения
    translateX: 0,         // Смещение по X
    translateY: 0,         // Смещение по Y
    videoStartTime: 0,     // Время начала видео
    videoEndTime: 15,      // Время окончания видео
    videoDuration: 0       // Продолжительность видео
};

// Основные методы
function processFile(file) { /* Обработка выбранного файла */ }
function showImageEditor(imageURL) { /* Показ редактора изображений */ }
function showVideoEditor(videoURL) { /* Показ редактора видео */ }
function handleSave() { /* Сохранение медиафайла */ }
function handleReset() { /* Сброс редактора */ }
```

## Требования к серверу

- PHP 8.0+
- Laravel 9+
- FFmpeg (для обработки видео)
- GD или Imagick (для обработки изображений)
- Достаточно места для хранения загруженных файлов

## Отладка

Для помощи в отладке редактор предоставляет консольные сообщения, которые можно включить в коде:

```javascript
const utils = {
    log: (message, data = null) => console.log(`[MediaEditor] ${message}`, data || '')
};
```

## Расширение функциональности

Для добавления новых возможностей:

1. Добавьте новые методы в JavaScript файл
2. Обновите контроллер для обработки новых параметров
3. Расширьте сервис обработки медиафайлов при необходимости
