# Система QR-кодов для шаблонов серий

## Описание

Система QR-кодов позволяет владельцам шаблонов-серий предоставлять доступ к своим шаблонам клиентам через сканирование QR-кодов, а также деактивировать использованные шаблоны.

## Архитектура

### Модели

1. **TemplateQrCode** - модель для хранения информации о QR-кодах
   - `template_id` - ID шаблона
   - `type` - тип QR-кода ('client' или 'creator')
   - `token` - уникальный токен
   - `expires_at` - время истечения
   - `is_used` - флаг использования
   - `created_by` - ID создателя
   - `used_by` - ID пользователя, который использовал
   - `used_at` - время использования

### Контроллер

**TemplateQrController** содержит следующие методы:

1. `generateClientQr()` - генерация QR-кода для клиента
2. `generateCreatorQr()` - генерация QR-кода для создателя
3. `claimViaQr()` - получение шаблона клиентом по QR-коду
4. `deactivateViaQr()` - деактивация шаблона создателем
5. `getQrStatus()` - получение статуса QR-кодов

### Маршруты

```php
// Публичные маршруты
Route::get('/templates/qr/claim/{token}', 'claimViaQr');
Route::get('/templates/qr/deactivate/{token}', 'deactivateViaQr');

// Защищенные маршруты
Route::post('/template-qr/{template}/generate-client', 'generateClientQr');
Route::post('/template-qr/{template}/generate-creator', 'generateCreatorQr');
Route::get('/template-qr/{template}/status', 'getQrStatus');
```

## Логика работы

### 1. Создание QR-кода для клиента

1. Владелец шаблона нажимает "Создать QR для клиента"
2. Система генерирует уникальный токен
3. Создается запись в `template_qr_codes` с типом 'client'
4. Генерируется QR-код с URL: `/templates/qr/claim/{token}`
5. QR-код сохраняется в storage и отображается владельцу

### 2. Получение шаблона клиентом

1. Клиент сканирует QR-код
2. Переходит по ссылке `/templates/qr/claim/{token}`
3. Система проверяет:
   - Действителен ли токен
   - Не истек ли срок действия
   - Есть ли доступные использования
   - Авторизован ли пользователь
4. Создается копия шаблона для клиента
5. Уменьшается `series_current` у оригинального шаблона
6. QR-код помечается как использованный

### 3. Создание QR-кода для деактивации

1. После использования клиентского QR-кода владелец может создать QR для деактивации
2. Генерируется новый токен с типом 'creator'
3. Создается QR-код с URL: `/templates/qr/deactivate/{token}`

### 4. Деактивация шаблона

1. Владелец сканирует QR-код деактивации
2. Система проверяет права доступа
3. Устанавливает `series_current = 0` (деактивирует шаблон)
4. QR-код помечается как использованный

## Frontend интерфейс

### HTML структура

```html
<div class="qr-code-section">
    <div class="qr-code-container">
        <!-- Начальное состояние -->
        <div id="noQrMessage">
            <button id="generateClientQr">Создать QR для клиента</button>
        </div>
        
        <!-- QR для клиента -->
        <div id="clientQrDisplay">
            <div id="clientQrImage"></div>
            <button id="generateCreatorQr">Создать QR для деактивации</button>
        </div>
        
        <!-- QR для создателя -->
        <div id="creatorQrDisplay">
            <div id="creatorQrImage"></div>
            <button id="resetQrCodes">Сбросить</button>
        </div>
    </div>
</div>
```

### JavaScript функции

1. `generateClientQrCode()` - генерация QR для клиента
2. `generateCreatorQrCode()` - генерация QR для создателя
3. `checkQrStatus()` - проверка статуса QR-кодов
4. `showClientQr()` / `showCreatorQr()` - отображение QR-кодов
5. `resetQrCodes()` - сброс интерфейса

## Безопасность

1. **Токены** - используются криптографически стойкие случайные токены
2. **Время жизни** - QR-коды имеют ограниченное время действия
3. **Однократное использование** - каждый QR-код можно использовать только один раз
4. **Проверка прав** - все операции проверяют права доступа пользователя

## Установка и настройка

### 1. Установка зависимостей

```bash
composer require simplesoftwareio/simple-qrcode
```

### 2. Миграция базы данных

```bash
php artisan migrate
```

### 3. Настройка хранилища

Убедитесь, что настроен storage для сохранения QR-кодов:

```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

## Тестирование

Для тестирования системы используйте файл `/public/qr-demo.html`, который содержит полную демонстрацию работы QR-интерфейса.

## API

### Генерация QR для клиента

```javascript
POST /template-qr/{template}/generate-client
Headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': token
}

Response: {
    "success": true,
    "qr_url": "/storage/qr-codes/client-123-1625097600.png",
    "token": "abc123...",
    "expires_at": "2025-07-05 10:30:00"
}
```

### Получение статуса QR

```javascript
GET /template-qr/{template}/status

Response: {
    "client_qr_active": true,
    "creator_qr_active": false,
    "client_qr_url": "/storage/qr-codes/client-123.png",
    "creator_qr_url": null,
    "series_current": 5,
    "series_max": 10
}
```

## Примечания

1. QR-коды автоматически очищаются при истечении срока действия
2. Система поддерживает только серийные шаблоны (`is_series = true`)
3. Для работы требуется аутентификация пользователей
4. Рекомендуется настроить очистку истекших QR-кодов через планировщик задач
