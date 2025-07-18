# QR-коды для серий шаблонов

## Обзор системы

Система QR-кодов позволяет создателям шаблонов-серий распространять свои шаблоны клиентам, а затем отслеживать их использование.

## Основные компоненты

### 1. Модели
- `UserTemplate` - шаблоны пользователей
- `TemplateQrCode` - QR-коды для шаблонов

### 2. Контроллер
- `TemplateQrController` - обработка QR-кодов

### 3. Маршруты
- `/template-qr/{id}/generate-client` - генерация QR для клиентов
- `/template-qr/{id}/generate-creator` - генерация QR для деактивации
- `/qr/claim/{token}` - получение шаблона клиентом
- `/qr/deactivate/{token}` - деактивация шаблона

## Логика работы

### Создание серии
1. Пользователь создает шаблон с `is_series = true`
2. Устанавливается `series_max` (общее количество)
3. Устанавливается `series_current` (доступное количество)

### Распространение шаблонов
1. **Оригинальный создатель** видит QR-код для клиентов
2. **Клиент сканирует QR** и получает копию шаблона
3. **Счетчик серии** уменьшается во всех шаблонах серии
4. **Новый шаблон** создается с названием "(Получено по QR)"

### Отслеживание использования
1. **Полученный шаблон** показывает QR-код деактивации
2. **Создатель сканирует QR** для подтверждения использования
3. **Шаблон помечается** как "(Использован)"

## Типы QR-кодов

### Client QR (для клиентов)
- Показывается оригинальному создателю серии
- Позволяет клиентам получить шаблон
- Действует 24 часа

### Creator QR (для деактивации)
- Показывается владельцам полученных шаблонов
- Позволяет создателю отметить шаблон как использованный
- Действует 1 час

## Состояния шаблонов

### Оригинальный шаблон серии
- `is_series = true`
- `series_max > 0`
- `series_current` уменьшается при получении
- Показывает Client QR

### Полученный шаблон
- `is_series = true`
- Название содержит "(Получено по QR)"
- Показывает Creator QR
- После деактивации: "(Использован)"

### Использованный шаблон
- Название содержит "(Использован)"
- `series_current = 0`
- QR-код не показывается

## Безопасность
- QR-коды имеют срок действия
- Токены уникальны и случайны (32 символа)
- Проверка прав доступа на всех этапах
- Защита от повторного использования
