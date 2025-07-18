{{-- Добавляем модальное окно для настройки серии --}}
@include('templates.components.series-modal')


{{-- Подключаем наши стили --}}
<link rel="stylesheet" href="/css/templates/date-picker-enhanced.css">
<link rel="stylesheet" href="/css/templates/simple-calendar.css">

{{-- Подключаем наши скрипты --}}
<script src="/js/templates/template-editor.js"></script>
<script src="/js/templates/template-data-collector.js"></script>
<script src="/js/templates/inline-editor.js"></script>
<script src="/js/template-series-handler.js"></script>
<script src="/js/template-series-handler.js"></script>
{{-- Тестовый скрипт для отладки работы с обложкой --}}
<script src="/js/templates/template-cover-test.js"></script>

<script>
    // Передаем данные для JavaScript - используем window для глобальных переменных
    if (typeof window.editableElementsJson === 'undefined') {
        window.editableElementsJson = @json($baseTemplate->editable_elements ?? []);
    }
    if (typeof window.baseTemplateId === 'undefined') {
        window.baseTemplateId = {{ isset($baseTemplate) && $baseTemplate ? $baseTemplate->id : 'null' }};
        console.log('Устанавливаем baseTemplateId:', window.baseTemplateId);
    }
    if (typeof window.removeUrl === 'undefined') {
        window.removeUrl = '{{ route("templates.remove-cover") }}';
    }
    if (typeof window.previewUrl === 'undefined') {
        window.previewUrl = '{{ route('templates.preview', $baseTemplate->id ?? 0) }}';
    }
    if (typeof window.storeUrl === 'undefined') {
        window.storeUrl = '{{ route("user-templates.store") }}';
    }
    if (typeof window.myTemplatesUrl === 'undefined') {
        window.myTemplatesUrl = '{{ route("my-templates") }}';
    }
    
    // Инициализируем глобальные настройки серии
    window.seriesSettings = window.seriesSettings || {
        isSeries: false,
        seriesMax: 10,
        seriesCurrent: 0,
        is_series: false,
        series_max: 10, 
        series_current: 0
    };
    
    // Инициализируем глобальные данные шаблона
    window.templateName = document.getElementById('template-name')?.value || `Мой шаблон ${new Date().toLocaleDateString('ru-RU')}`;
    
    // Привязываем обработчик к кнопкам сохранения (как для десктопа, так и для мобильной версии)
    document.addEventListener('DOMContentLoaded', function() {
        // Проверка, что необходимые объекты доступны
        if (typeof window.saveTemplateData === 'function') {
            console.log('✅ Функция saveTemplateData доступна глобально');
        } else {
            console.error('❌ Функция saveTemplateData не найдена');
        }
        
        if (window.TemplateEditor) {
            console.log('✅ Редактор шаблонов найден');
        } else {
            console.error('❌ Редактор шаблонов не найден');
        }
        
        if (window.TemplateDataCollector) {
            console.log('✅ Сборщик данных шаблонов найден');
            console.log('✅ Сборщик данных шаблонов инициализирован');
        } else {
            console.error('❌ Сборщик данных шаблонов не найден');
        }
        
        // Проверяем наличие кнопки в мобильной навигации
        const saveMediaBtn = document.getElementById('save-media-btn');
        if (saveMediaBtn) {
            console.log('✅ Найдена кнопка save-media-btn в мобильной навигации, добавляем обработчик');
            saveMediaBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('🔄 Нажата кнопка сохранения в мобильной навигации');
                if (window.saveTemplateData) {
                    window.saveTemplateData();
                } else {
                    console.error('Функция saveTemplateData не найдена');
                }
            });
        } else {
            console.log('❌ Кнопка save-media-btn не найдена');
        }
        
        // Проверяем наличие обычной кнопки (если она есть)
        const saveTemplateBtn = document.getElementById('save-template-btn');
        if (saveTemplateBtn) {
            console.log('✅ Найдена кнопка save-template-btn, добавляем обработчик');
            saveTemplateBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.saveTemplateData) {
                    window.saveTemplateData();
                } else {
                    console.error('Функция saveTemplateData не найдена');
                }
            });
        }
        
        // Отображаем предупреждение при попытке покинуть страницу с несохраненными изменениями
        window.addEventListener('beforeunload', function(e) {
            if (window.TemplateEditor && window.TemplateEditor.hasUnsavedChanges && window.TemplateEditor.hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = 'У вас есть несохраненные изменения. Вы уверены, что хотите покинуть страницу?';
            }
        });
    });
    
    // Проверяем наличие функции saveTemplateData
    if (!window.saveTemplateData) {
        console.error('❌ Функция saveTemplateData не найдена! Убедитесь, что скрипт template-data-collector.js подключен корректно');
    } else {
        console.log('✅ Функция saveTemplateData загружена успешно');
    }
</script>
