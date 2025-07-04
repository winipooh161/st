{{-- Компонент для прямого редактирования шаблона --}}
<div class="template-preview-container position-relative">
    {{-- <div id="loading-overlay" class="loading-overlay">
        <div class="d-flex flex-column align-items-center">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <div id="load-status" class="text-muted">Загрузка шаблона...</div>
        </div>
    </div> --}}

    {{-- Блок для прямого редактирования HTML-шаблона --}}
    <div id="template-content" class="template-content-wrapper border rounded p-3 overflow-auto">
        @php
            // Проверяем, находимся ли мы на странице создания шаблона
            $isCreatePage = isset($baseTemplate->isCreatePage) ? $baseTemplate->isCreatePage : false;
            
            // Если находимся на странице создания, то переменная $isCreatePage будет true
            // и будет использоваться в HTML-содержимом шаблона для скрытия блока серии
        @endphp
        
        {!! $baseTemplate->html_content ?? '<div class="alert alert-danger">Шаблон не загружен</div>' !!}
    </div>
</div>
