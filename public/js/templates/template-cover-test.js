/**
 * Тестовый скрипт для проверки сохранения шаблона с обложкой
 * Дата: 4 июля 2025 г.
 */

// Функция для тестирования сохранения шаблона
function testTemplateSave() {
    console.log('🧪 Начинаем тест сохранения шаблона...');
    
    // Проверяем наличие необходимых элементов
    console.log('📋 Проверка элементов:');
    console.log('- #template-content:', document.querySelector('#template-content'));
    console.log('- #template-name:', document.querySelector('#template-name'));
    console.log('- #coverPath:', document.querySelector('#coverPath'));
    console.log('- #coverType:', document.querySelector('#coverType'));
    console.log('- #coverThumbnail:', document.querySelector('#coverThumbnail'));
    
    // Проверяем значения обложки
    const coverPath = document.querySelector('#coverPath')?.value;
    const coverType = document.querySelector('#coverType')?.value;
    const coverThumbnail = document.querySelector('#coverThumbnail')?.value;
    
    console.log('🖼️ Данные обложки:');
    console.log('- coverPath:', coverPath);
    console.log('- coverType:', coverType);
    console.log('- coverThumbnail:', coverThumbnail);
    
    // Проверяем данные о дате
    const dateElements = document.querySelectorAll('[data-field-type="date"]');
    console.log('📅 Элементы даты:');
    dateElements.forEach((element, index) => {
        console.log(`- Элемент ${index + 1}:`, {
            text: element.innerText,
            fieldName: element.getAttribute('data-editable'),
            format: element.getAttribute('data-date-format'),
            minDate: element.getAttribute('data-min-date'),
            maxDate: element.getAttribute('data-max-date'),
            locale: element.getAttribute('data-locale')
        });
    });
    
    // Проверяем глобальные переменные
    console.log('🌐 Глобальные переменные:');
    console.log('- window.baseTemplateId:', window.baseTemplateId);
    console.log('- window.storeUrl:', window.storeUrl);
    console.log('- window.seriesSettings:', window.seriesSettings);
    console.log('- window.saveTemplateData:', typeof window.saveTemplateData);
    console.log('- window.TemplateDataCollector:', typeof window.TemplateDataCollector);
    
    // Тестируем сбор данных
    if (window.TemplateDataCollector && window.TemplateDataCollector.collectData) {
        const collectedData = window.TemplateDataCollector.collectData();
        console.log('📊 Собранные данные:', collectedData);
    } else {
        console.error('❌ TemplateDataCollector недоступен');
    }
    
    console.log('✅ Тест завершен');
}

// Функция для имитации сохранения шаблона с тестовыми данными
function simulateTemplateSave() {
    console.log('🚀 Имитация сохранения шаблона...');
    
    // Устанавливаем тестовые данные обложки
    const testCoverPath = 'test/cover/image.jpg';
    const testCoverType = 'image';
    const testCoverThumbnail = 'test/cover/thumbnail.jpg';
    
    // Обновляем скрытые поля
    let coverPathElement = document.querySelector('#coverPath');
    let coverTypeElement = document.querySelector('#coverType');
    let coverThumbnailElement = document.querySelector('#coverThumbnail');
    
    if (!coverPathElement) {
        coverPathElement = document.createElement('input');
        coverPathElement.type = 'hidden';
        coverPathElement.id = 'coverPath';
        document.body.appendChild(coverPathElement);
    }
    
    if (!coverTypeElement) {
        coverTypeElement = document.createElement('input');
        coverTypeElement.type = 'hidden';
        coverTypeElement.id = 'coverType';
        document.body.appendChild(coverTypeElement);
    }
    
    if (!coverThumbnailElement) {
        coverThumbnailElement = document.createElement('input');
        coverThumbnailElement.type = 'hidden';
        coverThumbnailElement.id = 'coverThumbnail';
        document.body.appendChild(coverThumbnailElement);
    }
    
    coverPathElement.value = testCoverPath;
    coverTypeElement.value = testCoverType;
    coverThumbnailElement.value = testCoverThumbnail;
    
    console.log('📋 Установлены тестовые данные обложки:', {
        path: testCoverPath,
        type: testCoverType,
        thumbnail: testCoverThumbnail
    });
    
    // Выполняем тест сбора данных
    testTemplateSave();
    
    // Имитируем сохранение (без отправки на сервер)
    if (window.TemplateDataCollector && window.TemplateDataCollector.collectData) {
        const data = window.TemplateDataCollector.collectData();
        console.log('💾 Данные, которые будут отправлены на сервер:', {
            name: data.name || 'Шаблон от ' + new Date().toLocaleDateString('ru-RU'),
            description: data.description || '',
            base_template_id: window.baseTemplateId || null,
            template_html: document.querySelector('#template-content')?.innerHTML || '',
            editable_data: data.elements,
            cover_path: data.cover.path,
            cover_type: data.cover.type,
            cover_thumbnail: data.cover.thumbnail,
            template_date: data.date?.date,
            date_format: data.date?.format,
            date_settings: data.date?.settings
        });
    }
}

// Экспорт функций в глобальную область
window.testTemplateSave = testTemplateSave;
window.simulateTemplateSave = simulateTemplateSave;

// Автоматический запуск теста при загрузке страницы (с задержкой)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        console.log('🔄 Автоматический запуск теста через 2 секунды...');
        testTemplateSave();
    }, 2000);
});
