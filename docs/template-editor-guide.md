# Документация по редактору шаблонов

## Общая информация

Редактор шаблонов - это JavaScript-библиотека для прямого редактирования текстовых элементов и полей с датами в шаблонах документов. Основные возможности:

- Прямое редактирование текста (без использования отдельных input-полей)
- Встроенный календарь для выбора дат
- Автоматическое сохранение данных
- Уведомления об успешном сохранении или ошибках

## Подключение редактора

Для использования редактора необходимо подключить файлы:

```html
<!-- Подключение стилей -->
<link rel="stylesheet" href="{{ asset('css/template-editor-unified.css') }}">

<!-- Подключение скрипта -->
<script src="{{ asset('js/templates/template-editor-unified.js') }}"></script>
```

## Использование

### Разметка HTML

Для создания редактируемого текстового элемента:

```html
<span data-editable="true" data-editable-field="field_id">Редактируемый текст</span>
```

Для создания редактируемого поля с датой:

```html
<span data-editable="true" data-editable-field="date_field_id" data-date-field="true">01.01.2025</span>
```

### Атрибуты элементов

- `data-editable="true"` - делает элемент редактируемым
- `data-editable-field="field_id"` - задает уникальный идентификатор поля
- `data-date-field="true"` - указывает, что поле содержит дату

### JavaScript API

Основные методы доступны через глобальный объект `TemplateEditor`:

```javascript
// Инициализация редактируемых элементов
TemplateEditor.initializeEditableElements();

// Инициализация полей с датами
TemplateEditor.initializeDateFields();

// Получение данных всех редактируемых полей
const data = TemplateEditor.getEditableData();

// Сохранение данных шаблона
TemplateEditor.saveTemplateData();
```

## Интеграция с сервером

По умолчанию редактор отправляет данные на сервер по следующему URL:

```
POST /api/template-fields/update
```

Формат отправляемых данных:

```json
{
  "template_id": "template-1",
  "fields": {
    "field_id": {
      "value": "Новое значение",
      "type": "text"
    },
    "date_field_id": {
      "value": "01.07.2025",
      "type": "date"
    }
  }
}
```

## Кастомизация

### Настройка календаря

Для настройки календаря можно изменить объект `config` в файле `template-editor-unified.js`:

```javascript
config: {
    // CSS классы для различных состояний элементов
    css: {
        editable: 'template-editable',
        editing: 'template-editing',
        dateField: 'template-date-field',
        error: 'template-edit-error',
        saved: 'template-edit-saved',
        hover: 'template-edit-hover'
    },
    // Задержка автосохранения в миллисекундах (0 - отключено)
    autoSaveDelay: 0,
    // Локализация календаря
    locale: {
        months: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
        weekdays: {
            shorthand: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб'],
            longhand: ['Воскресенье', 'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота']
        },
        firstDayOfWeek: 1
    }
}
```

## Устранение неполадок

### Редактор не работает

1. Проверьте, что файлы `template-editor-unified.js` и `template-editor-unified.css` подключены корректно
2. Проверьте, что атрибуты `data-editable` и `data-editable-field` заданы правильно
3. Проверьте консоль браузера на наличие ошибок

### Календарь не отображается

1. Проверьте, что атрибут `data-date-field="true"` добавлен к элементу
2. Проверьте, что библиотека flatpickr загружается корректно (смотрите консоль браузера)

### Данные не сохраняются

1. Проверьте CSRF-токен: `<meta name="csrf-token" content="{{ csrf_token() }}">`
2. Проверьте URL для сохранения данных
3. Проверьте консоль на наличие ошибок при отправке запроса

## Пример использования в шаблоне

```html
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="template-id" content="{{ $template->id }}">
    
    <link rel="stylesheet" href="{{ asset('css/template-editor-unified.css') }}">
</head>
<body>
    <div class="template-container">
        <div class="template-toolbar">
            <div class="toolbar-left">
                <h2>Редактирование шаблона</h2>
            </div>
            <div class="toolbar-right">
                <button class="template-save-btn" onclick="TemplateEditor.saveTemplateData()">
                    <i class="fas fa-save"></i> Сохранить
                </button>
            </div>
        </div>
        
        <div class="template-preview">
            <h1 data-editable="true" data-editable-field="title">Заголовок документа</h1>
            
            <p>Дата: <span data-editable="true" data-editable-field="date" data-date-field="true">01.01.2025</span></p>
            
            <p data-editable="true" data-editable-field="content">
                Содержание документа, которое можно редактировать прямо в этом элементе.
            </p>
        </div>
    </div>
    
    <script src="{{ asset('js/templates/template-editor-unified.js') }}"></script>
</body>
</html>
```

## Совместимость

Редактор совместим со всеми современными браузерами:
- Chrome (последние версии)
- Firefox (последние версии)
- Safari (последние версии)
- Edge (последние версии)
