{{-- Пример подключения редактора шаблонов --}}

{{-- Добавляем мета-данные для шаблона --}}
<meta name="template-id" content="test-template-1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="template-preview-container">
    {{-- Оверлей для индикации загрузки --}}
    <div id="loading-overlay" class="loading-overlay"></div>
    
    {{-- Контейнер с содержимым шаблона --}}
    <div id="template-content" class="template-content">
        <div class="document">
            <h1 data-editable="title" data-field-type="text">Заголовок документа</h1>
            
            <div class="document-meta">
                <p>Дата: <span data-editable="document_date" data-field-type="date">01.07.2025</span></p>
                <p>Номер: <span data-editable="document_number" data-field-type="text">12345</span></p>
            </div>
            
            <div class="document-content">
                <h2>Описание</h2>
                <div data-editable="description" data-field-type="textarea">
                    Это многострочный текст описания документа, который можно редактировать.
                    
                    Редактируемый текст поддерживает многострочное форматирование.
                </div>
                
                <h2>Дополнительные данные</h2>
                <ul>
                    <li>Поле 1: <span data-editable="field1" data-field-type="text">Значение 1</span></li>
                    <li>Поле 2: <span data-editable="field2" data-field-type="text">Значение 2</span></li>
                    <li>Срок действия: <span data-editable="expiry_date" data-field-type="date">31.12.2025</span></li>
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- Подключаем загрузчик редактора шаблонов --}}
<script src="/js/templates/template-editor-loader.js"></script>
