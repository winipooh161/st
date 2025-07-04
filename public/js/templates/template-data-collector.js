/**
 * Сборщик данных шаблона
 * Отвечает за сбор и сохранение данных редактируемых элементов
 * Дата: 4 июля 2025 г.
 */

// Создаем объект TemplateDataCollector
const TemplateDataCollector = (function() {
    // Состояние
    const state = {
        baseTemplateId: null,
        editableElements: {},
        templateName: '',
        templateDescription: '',
        saveInProgress: false,
        lastSavedData: null,
        unsavedChanges: false // Флаг наличия несохраненных изменений
    };
    
    // Конфигурация
    const config = {
        editableSelector: '[data-editable]',
        saveButtonSelector: '#save-template-btn, #save-media-btn',
        nameInputSelector: '#template-name',
        descriptionInputSelector: '#template-description',
        loadingOverlayClass: 'loading-overlay',
        feedbackMessageClass: 'feedback-message'
    };
    
    /**
     * Конвертирует дату из отображаемого формата в формат БД (YYYY-MM-DD)
     * @param {string} dateString - дата в формате отображения (например, "01.01.2025")
     * @returns {string|null} дата в формате YYYY-MM-DD или null
     */
    function convertDateToDbFormat(dateString) {
        if (!dateString || dateString === 'Выберите дату') {
            return null;
        }
        
        try {
            // Пытаемся распарсить дату в различных форматах
            let date;
            
            // Формат dd.mm.yyyy
            if (dateString.includes('.')) {
                const parts = dateString.split('.');
                if (parts.length === 3) {
                    date = new Date(parts[2], parts[1] - 1, parts[0]);
                }
            }
            // Формат yyyy-mm-dd (уже в правильном формате)
            else if (dateString.includes('-')) {
                date = new Date(dateString);
            }
            // Формат dd/mm/yyyy
            else if (dateString.includes('/')) {
                const parts = dateString.split('/');
                if (parts.length === 3) {
                    date = new Date(parts[2], parts[1] - 1, parts[0]);
                }
            }
            else {
                return null;
            }
            
            // Проверяем, что дата валидна
            if (date && !isNaN(date.getTime())) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }
            
            return null;
        } catch (error) {
            console.error('Ошибка при конвертации даты:', error);
            return null;
        }
    }
    
    /**
     * Сбор данных из всех редактируемых элементов
     * @returns {Object} собранные данные
     */
    function collectData() {
        const editableElements = document.querySelectorAll(config.editableSelector);
        const data = {};
        
        // Собираем данные из редактируемых элементов
        editableElements.forEach(element => {
            const fieldName = element.getAttribute('data-editable');
            const fieldType = element.getAttribute('data-field-type') || 'text';
            const value = element.innerText.trim();
            
            data[fieldName] = {
                value: value,
                type: fieldType
            };
        });
        
        // Получаем имя и описание шаблона
        const nameInput = document.querySelector(config.nameInputSelector);
        const descriptionInput = document.querySelector(config.descriptionInputSelector);
        
        console.log('Поиск элементов имени и описания:');
        console.log('nameInput элемент:', nameInput);
        console.log('descriptionInput элемент:', descriptionInput);
        
        if (nameInput) {
            state.templateName = nameInput.value.trim();
            console.log('Найден элемент имени со значением:', state.templateName);
        } else {
            state.templateName = ''; // Устанавливаем пустую строку, если элемент не найден
            console.log('Элемент имени не найден, устанавливаем пустую строку');
        }
        
        if (descriptionInput) {
            state.templateDescription = descriptionInput.value.trim();
            console.log('Найден элемент описания со значением:', state.templateDescription);
        } else {
            state.templateDescription = ''; // Устанавливаем пустую строку, если элемент не найден
            console.log('Элемент описания не найден, устанавливаем пустую строку');
        }
        
        // Обновляем состояние
        state.editableElements = data;
        
        // Получаем данные обложки
        const coverPathElement = document.querySelector('#coverPath');
        const coverTypeElement = document.querySelector('#coverType');
        const coverThumbnailElement = document.querySelector('#coverThumbnail');
        
        const coverData = {
            path: coverPathElement ? coverPathElement.value : null,
            type: coverTypeElement ? coverTypeElement.value : null,
            thumbnail: coverThumbnailElement ? coverThumbnailElement.value : null
        };
        
        // Получаем данные о дате из редактируемых элементов с типом date
        const dateData = {
            date: null,
            format: null,
            settings: {}
        };
        
        // Получаем данные диапазона дат для отправки на сервер
        const dateRangeData = {
            date_from: null,
            date_to: null
        };
        
        // Ищем элементы с типом date
        editableElements.forEach(element => {
            const fieldType = element.getAttribute('data-field-type');
            if (fieldType === 'date') {
                const fieldName = element.getAttribute('data-editable');
                const value = element.innerText.trim();
                
                // Сохраняем дату для общих данных (совместимость с старым кодом)
                if (value && value !== 'Выберите дату') {
                    dateData.date = value;
                    dateData.format = element.getAttribute('data-date-format') || 'd.m.Y';
                    
                    // Сохраняем дополнительные настройки даты
                    const minDate = element.getAttribute('data-min-date');
                    const maxDate = element.getAttribute('data-max-date');
                    const locale = element.getAttribute('data-locale');
                    
                    if (minDate || maxDate || locale) {
                        dateData.settings = {
                            minDate: minDate || null,
                            maxDate: maxDate || null,
                            locale: locale || 'ru'
                        };
                    }
                }
                
                // Обрабатываем диапазон дат
                if (value && value !== 'Выберите дату') {
                    // Конвертируем дату в формат YYYY-MM-DD для базы данных
                    const dbDate = convertDateToDbFormat(value);
                    
                    if (fieldName === 'start_date' || fieldName === 'date_from') {
                        dateRangeData.date_from = dbDate;
                    } else if (fieldName === 'end_date' || fieldName === 'date_to') {
                        dateRangeData.date_to = dbDate;
                    }
                }
                
                console.log('🗓️ Найден элемент даты:', {
                    fieldName,
                    value,
                    dbDate: convertDateToDbFormat(value),
                    format: dateData.format,
                    settings: dateData.settings
                });
            }
        });
        
        // Отладочная информация для диагностики работы с обложкой
        console.log('🔍 Диагностика данных обложки:');
        console.log('- Элементы обложки:', {
            coverPath: document.querySelector('#coverPath'),
            coverType: document.querySelector('#coverType'),
            coverThumbnail: document.querySelector('#coverThumbnail')
        });
        console.log('- Значения элементов обложки:', {
            coverPath: document.querySelector('#coverPath')?.value,
            coverType: document.querySelector('#coverType')?.value,
            coverThumbnail: document.querySelector('#coverThumbnail')?.value
        });
        console.log('- Данные обложки из collectData:', coverData);
        
        return {
            name: state.templateName,
            description: state.templateDescription,
            baseTemplateId: state.baseTemplateId,
            elements: data,
            cover: coverData,
            date: dateData,
            dateRange: dateRangeData // Добавляем диапазон дат
        };
    }
    
    /**
     * Показ оверлея загрузки
     * @param {string} message - сообщение для пользователя
     */
    function showLoadingOverlay(message = 'Сохранение шаблона...') {
        // Проверяем, существует ли уже оверлей
        let overlay = document.querySelector(`.${config.loadingOverlayClass}`);
        
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = config.loadingOverlayClass;
            document.body.appendChild(overlay);
        }
        
        // Добавляем индикатор загрузки и сообщение
        overlay.innerHTML = `
            <div class="loading-spinner"></div>
            <div class="loading-message">${message}</div>
        `;
        
        overlay.style.display = 'flex';
    }
    
    /**
     * Скрытие оверлея загрузки
     */
    function hideLoadingOverlay() {
        const overlay = document.querySelector(`.${config.loadingOverlayClass}`);
        
        if (overlay) {
            overlay.style.display = 'none';
        }
    }
    
    /**
     * Показ сообщения пользователю
     * @param {string} message - текст сообщения
     * @param {string} type - тип сообщения (success, error, warning, info)
     * @param {number} duration - продолжительность показа в миллисекундах
     */
    function showFeedbackMessage(message, type = 'success', duration = 3000) {
        // Проверяем, существует ли уже элемент сообщения
        let messageElement = document.querySelector(`.${config.feedbackMessageClass}`);
        
        if (!messageElement) {
            messageElement = document.createElement('div');
            messageElement.className = config.feedbackMessageClass;
            document.body.appendChild(messageElement);
        }
        
        // Задаем тип сообщения
        messageElement.className = `${config.feedbackMessageClass} ${type}`;
        messageElement.textContent = message;
        messageElement.style.display = 'block';
        
        // Автоматически скрываем сообщение через указанное время
        setTimeout(() => {
            messageElement.style.display = 'none';
        }, duration);
    }
    
    /**
     * Сохранение данных шаблона
     * @param {Event|Object} e - событие или объект с настройками серии
     */
    async function saveTemplateData(e) {
        // Проверяем, является ли e событием или объектом настроек
        if (e && e.preventDefault && typeof e.preventDefault === 'function') {
            e.preventDefault();
        }
        
        // Предотвращаем повторное сохранение
        if (state.saveInProgress) {
            console.log('Сохранение уже выполняется, пожалуйста, подождите...');
            return;
        }
        
        state.saveInProgress = true;
        
        // Сохраняем настройки серии, если они были переданы
        let seriesSettings = null;
        if (e && typeof e === 'object' && !e.preventDefault) {
            seriesSettings = e;
        }
        
        try {
            // Собираем данные
            const templateData = collectData();
            
            console.log('Собранные данные шаблона:', templateData);
            
            // Используем значение по умолчанию для имени шаблона, если оно не указано
            if (!templateData.name || templateData.name.trim() === '') {
                // Используем текущую дату для имени шаблона
                const now = new Date();
                const defaultName = `Шаблон от ${now.getDate()}.${now.getMonth() + 1}.${now.getFullYear()}`;
                templateData.name = defaultName;
                console.log(`Имя шаблона не указано, используем значение по умолчанию: ${defaultName}`);
            } else {
                console.log(`Используем указанное имя шаблона: ${templateData.name}`);
            }
            
            // Показываем оверлей загрузки
            showLoadingOverlay();
            
            // Получаем HTML содержимое шаблона
            const templateContentElement = document.querySelector('#template-content');
            const templateHtml = templateContentElement ? templateContentElement.innerHTML : '';
            
            console.log('Проверка обязательных полей:');
            console.log('- baseTemplateId:', window.baseTemplateId);
            console.log('- templateHtml length:', templateHtml.length);
            console.log('- templateHtml preview:', templateHtml.substring(0, 100) + '...');
            console.log('- editable_data:', templateData.elements);
            console.log('- cover_path:', templateData.cover.path);
            console.log('- cover_type:', templateData.cover.type);
            console.log('- cover_thumbnail:', templateData.cover.thumbnail);
            console.log('- template_date:', templateData.date.date);
            console.log('- date_format:', templateData.date.format);
            console.log('- date_settings:', templateData.date.settings);
            console.log('- date_from:', templateData.dateRange.date_from);
            console.log('- date_to:', templateData.dateRange.date_to);
            
            // Формируем данные для отправки
            const postData = {
                name: templateData.name,
                description: templateData.description || '',
                base_template_id: window.baseTemplateId || null,
                template_html: templateHtml, // HTML содержимое шаблона
                editable_data: templateData.elements // Данные редактируемых элементов как объект
            };
            
            // Добавляем данные обложки, если они есть
            if (templateData.cover.path) {
                postData.cover_path = templateData.cover.path;
                postData.cover_type = templateData.cover.type;
                if (templateData.cover.thumbnail) {
                    postData.cover_thumbnail = templateData.cover.thumbnail;
                }
            }
            
            // Добавляем данные о дате, если они есть
            if (templateData.date.date) {
                postData.template_date = templateData.date.date;
                postData.date_format = templateData.date.format;
                if (Object.keys(templateData.date.settings).length > 0) {
                    postData.date_settings = templateData.date.settings;
                }
            }
            
            // Добавляем диапазон дат, если они есть
            if (templateData.dateRange.date_from) {
                postData.date_from = templateData.dateRange.date_from;
                console.log('✅ Добавлена дата "от":', postData.date_from);
            }
            if (templateData.dateRange.date_to) {
                postData.date_to = templateData.dateRange.date_to;
                console.log('✅ Добавлена дата "до":', postData.date_to);
            }
            
            // Добавляем настройки серии, если они были переданы или существуют глобально
            if (seriesSettings) {
                console.log('Добавляем переданные настройки серии:', seriesSettings);
                postData.is_series = seriesSettings.is_series !== undefined ? seriesSettings.is_series : seriesSettings.isSeries;
                postData.series_max = seriesSettings.series_max !== undefined ? seriesSettings.series_max : seriesSettings.seriesMax;
                postData.series_current = seriesSettings.series_current !== undefined ? seriesSettings.series_current : seriesSettings.seriesCurrent;
            } else if (window.seriesSettings) {
                console.log('Добавляем глобальные настройки серии:', window.seriesSettings);
                postData.is_series = window.seriesSettings.is_series !== undefined ? window.seriesSettings.is_series : window.seriesSettings.isSeries;
                postData.series_max = window.seriesSettings.series_max !== undefined ? window.seriesSettings.series_max : window.seriesSettings.seriesMax;
                postData.series_current = window.seriesSettings.series_current !== undefined ? window.seriesSettings.series_current : window.seriesSettings.seriesCurrent;
            }
            
            console.log('Отправка данных:', postData);
            
            // Получаем CSRF-токен из мета-тега (Laravel)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Отправляем запрос на сервер
            const response = await fetch(window.storeUrl || '/user-templates', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(postData)
            });
            
            // Обрабатываем ответ
            if (response.ok) {
                const data = await response.json();
                console.log('Шаблон сохранен успешно:', data);
                
                // Сохраняем последние данные
                state.lastSavedData = templateData;
                
                showFeedbackMessage('Шаблон успешно сохранен!', 'success');
                
                // Переходим на страницу "Мои шаблоны"
                setTimeout(() => {
                    if (data.redirect_url) {
                        // Если сервер вернул конкретный URL, используем его
                        window.location.href = data.redirect_url;
                    } else {
                        // Иначе переходим на страницу "Мои шаблоны"
                        window.location.href = window.myTemplatesUrl || '/my-templates';
                    }
                }, 1000);
            } else {
                const errorData = await response.json();
                console.error('Ошибка при сохранении шаблона:', errorData);
                
                // Показываем подробную информацию об ошибке валидации
                if (errorData.errors) {
                    console.error('Детали ошибок валидации:', errorData.errors);
                    const errorMessages = Object.entries(errorData.errors)
                        .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
                        .join('\n');
                    showFeedbackMessage(`Ошибка валидации:\n${errorMessages}`, 'error');
                } else {
                    showFeedbackMessage(`Ошибка при сохранении шаблона: ${errorData.message || 'Неизвестная ошибка'}`, 'error');
                }
            }
        } catch (error) {
            console.error('Ошибка при сохранении шаблона:', error);
            showFeedbackMessage(`Произошла ошибка при сохранении: ${error.message}`, 'error');
        } finally {
            hideLoadingOverlay();
            state.saveInProgress = false;
        }
    }
    
    /**
     * Обновление поля в хранилище данных
     * @param {string} fieldId - идентификатор поля
     * @param {string} value - значение поля
     * @param {string} fieldType - тип поля (text, date и т.д.)
     */
    function updateField(fieldId, value, fieldType) {
        console.log(`📊 Обновление поля ${fieldId} в хранилище данных: ${value} (${fieldType})`);
        
        // Создаем объект поля если его нет
        if (!state.editableElements[fieldId]) {
            state.editableElements[fieldId] = {
                value: value,
                type: fieldType
            };
        } else {
            state.editableElements[fieldId].value = value;
        }
    }

    /**
     * Обновление данных обложки
     * @param {string} path - путь к файлу обложки
     * @param {string} type - тип обложки (image, video)
     * @param {string} thumbnail - миниатюра (для видео)
     */
    function updateCoverData(path, type, thumbnail = null) {
        console.log('📷 Обновление данных обложки:', { path, type, thumbnail });
        
        // Обновляем или создаем скрытые поля
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
        
        // Устанавливаем значения
        coverPathElement.value = path || '';
        coverTypeElement.value = type || '';
        coverThumbnailElement.value = thumbnail || '';
        
        // Устанавливаем флаг несохраненных изменений
        setUnsavedChanges(true);
        
        console.log('✅ Данные обложки обновлены');
    }

    /**
     * Обновление данных о дате в шаблоне
     * @param {string} date - выбранная дата
     * @param {string} format - формат даты
     * @param {Object} settings - дополнительные настройки
     */
    function updateDateData(date, format = 'd.m.Y', settings = {}) {
        console.log('📅 Обновление данных о дате:', { date, format, settings });
        
        // Находим элемент с типом date и обновляем его
        const dateElement = document.querySelector('[data-field-type="date"]');
        if (dateElement) {
            dateElement.innerText = date;
            dateElement.setAttribute('data-date-format', format);
            
            // Устанавливаем дополнительные атрибуты
            if (settings.minDate) {
                dateElement.setAttribute('data-min-date', settings.minDate);
            }
            if (settings.maxDate) {
                dateElement.setAttribute('data-max-date', settings.maxDate);
            }
            if (settings.locale) {
                dateElement.setAttribute('data-locale', settings.locale);
            }
        }
        
        // Устанавливаем флаг несохраненных изменений
        setUnsavedChanges(true);
        
        console.log('✅ Данные о дате обновлены');
    }

    /**
     * Установка флага несохраненных изменений
     * @param {boolean} hasChanges - флаг наличия несохраненных изменений
     */
    function setUnsavedChanges(hasChanges) {
        state.unsavedChanges = hasChanges;
        
        // Визуальное отображение статуса
        const saveButtons = document.querySelectorAll(config.saveButtonSelector);
        saveButtons.forEach(button => {
            if (hasChanges) {
                button.classList.add('btn-unsaved');
            } else {
                button.classList.remove('btn-unsaved');
            }
        });
    }

    /**
     * Инициализация сборщика данных
     */
    function init() {
        console.log('Инициализация сборщика данных шаблона...');
        
        // Получаем ID базового шаблона, если есть
        if (typeof window.baseTemplateId !== 'undefined') {
            state.baseTemplateId = window.baseTemplateId;
            console.log(`ID базового шаблона: ${state.baseTemplateId}`);
        }
        
        // Инициализируем глобальную функцию сохранения
        window.saveTemplateData = saveTemplateData;
        
        // Добавляем обработчики событий для кнопок сохранения
        document.querySelectorAll(config.saveButtonSelector).forEach(button => {
            button.addEventListener('click', saveTemplateData);
        });
        
        // Добавляем обработчик клика по обложке для перехода к медиа-редактору
        const coverContainer = document.querySelector('#coverContainer');
        if (coverContainer) {
            coverContainer.style.cursor = 'pointer';
            coverContainer.addEventListener('click', function() {
                console.log('🖼️ Клик по обложке - переход к медиа-редактору');
                window.location.href = '/media/editor?return_url=' + encodeURIComponent(window.location.href);
            });
        }
        
        // Подписываемся на события изменений в редакторе
        document.addEventListener('template-editor:change', function(e) {
            const { fieldName, value, fieldType } = e.detail;
            
            // Обновляем состояние
            state.editableElements[fieldName] = {
                value: value,
                type: fieldType
            };
        });
        
        // Стили для сообщений обратной связи
        addFeedbackStyles();
        
        console.log('Сборщик данных шаблона инициализирован успешно');
    }
    
    /**
     * Добавление стилей для сообщений и оверлея
     */
    function addFeedbackStyles() {
        const styleElement = document.createElement('style');
        styleElement.textContent = `
            .loading-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(255, 255, 255, 0.8);
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                z-index: 9999;
                backdrop-filter: blur(2px);
            }
            
            .loading-spinner {
                width: 40px;
                height: 40px;
                border: 4px solid rgba(0, 123, 255, 0.3);
                border-top: 4px solid #007bff;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }
            
            .loading-message {
                margin-top: 10px;
                font-size: 16px;
                color: #333;
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .feedback-message {
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 20px;
                border-radius: 4px;
                font-size: 14px;
                z-index: 9999;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                display: none;
                animation: fadeInRight 0.3s ease;
            }
            
            .feedback-message.success {
                background-color: #d4edda;
                color: #155724;
                border-left: 4px solid #28a745;
            }
            
            .feedback-message.error {
                background-color: #f8d7da;
                color: #721c24;
                border-left: 4px solid #dc3545;
            }
            
            .feedback-message.warning {
                background-color: #fff3cd;
                color: #856404;
                border-left: 4px solid #ffc107;
            }
            
            .feedback-message.info {
                background-color: #d1ecf1;
                color: #0c5460;
                border-left: 4px solid #17a2b8;
            }
            
            @keyframes fadeInRight {
                from {
                    opacity: 0;
                    transform: translateX(20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
         
        `;
        
        document.head.appendChild(styleElement);
    }
    
    // Возвращаем публичное API
    return {
        init,
        saveTemplateData,
        collectData,
        updateField,
        setUnsavedChanges,
        updateCoverData,
        updateDateData
    };
})();

// Экспорт в глобальную область видимости 
window.TemplateDataCollector = TemplateDataCollector;

// Немедленно создаем глобальную функцию saveTemplateData для доступа из других скриптов
window.saveTemplateData = function(e) {
    if (window.TemplateDataCollector && window.TemplateDataCollector.saveTemplateData) {
        return window.TemplateDataCollector.saveTemplateData(e);
    } else {
        console.error('Функция saveTemplateData из TemplateDataCollector недоступна');
        return false;
    }
};

// Глобальная функция для обновления данных обложки
window.updateCoverData = function(path, type, thumbnail = null) {
    if (window.TemplateDataCollector && window.TemplateDataCollector.updateCoverData) {
        return window.TemplateDataCollector.updateCoverData(path, type, thumbnail);
    } else {
        console.error('Функция updateCoverData из TemplateDataCollector недоступна');
        return false;
    }
};

// Глобальная функция для обновления данных о дате
window.updateDateData = function(date, format = 'd.m.Y', settings = {}) {
    if (window.TemplateDataCollector && window.TemplateDataCollector.updateDateData) {
        return window.TemplateDataCollector.updateDateData(date, format, settings);
    } else {
        console.error('Функция updateDateData из TemplateDataCollector недоступна');
        return false;
    }
};

// Инициализация при загрузке документа и немедленное выполнение для доступности
document.addEventListener('DOMContentLoaded', function() {
    if (typeof TemplateDataCollector !== 'undefined' && TemplateDataCollector.init) {
        TemplateDataCollector.init();
        console.log('✅ TemplateDataCollector успешно инициализирован при загрузке страницы');
    } else {
        console.error('❌ TemplateDataCollector не найден или не имеет метода init');
    }
});

// Выполняем инициализацию немедленно для доступности в других скриптах
(function() {
    if (typeof TemplateDataCollector !== 'undefined' && TemplateDataCollector.init) {
        try {
            TemplateDataCollector.init();
            console.log('✅ TemplateDataCollector инициализирован немедленно');
        } catch (e) {
            console.error('❌ Ошибка при немедленной инициализации TemplateDataCollector:', e);
        }
    }
})();

