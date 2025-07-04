
@if(isset($enableSaveButton) && $enableSaveButton)
<div class="template-toolbar">
    <div class="toolbar-left">
        <h2>{{ $title ?? 'Редактирование шаблона' }}</h2>
    </div>
    <div class="toolbar-right">
        <button class="template-save-btn" onclick="window.TemplateEditor.saveTemplateData()">
            <i class="fas fa-save"></i> Сохранить
        </button>
    </div>
</div>
@endif

{{-- Если нужно инициализировать редактор явно --}}
@if(isset($initScript) && $initScript)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Дополнительная инициализация редактора при необходимости
        console.log('🔄 Инициализация редактора шаблонов из компонента');
    });
</script>
@endif
