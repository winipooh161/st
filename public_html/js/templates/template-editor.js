/**
 * Редактор шаблонов
 * Включает в себя функциональность для редактирования текста и дат с помощью календаря
 * Дата: 4 июля 2025 г.
 */

// Создаем объект TemplateEditor
const TemplateEditor = (function() {
    // Хранение состояния редактора
    const state = {
        activeElement: null,
        datePickerInstances: {},
        editableElements: {},
        unsavedChanges: false,
        calendar: null
    };
    
    // Конфигурация 
    const config = {
        editableSelector: "[data-editable]",
        dateFieldSelector: "[data-field-type=\"date\"]",
        editingClass: "editing",
        dateFormat: "DD.MM.YYYY",
        calendarContainerId: "simple-calendar-container"
    };
    
    /**
     * Инициализация редактора
     */
    function init() {
        console.log("Инициализация редактора шаблонов...");
        
        // Применяем предварительно сохраненные значения, если они есть
        if (typeof window.editableElementsJson !== "undefined") {
            try {
                // Проверяем тип данных и применяем соответствующую обработку
                let elements;
                
                if (typeof window.editableElementsJson === 'string') {
                    // Если это строка JSON, разбираем её
                    elements = JSON.parse(window.editableElementsJson);
                } else if (typeof window.editableElementsJson === 'object') {
                    // Если это уже объект JavaScript, используем его напрямую
                    elements = window.editableElementsJson;
                }
                
                if (elements) {
                    state.editableElements = elements;
                    console.log("✅ Применены сохраненные значения для редактируемых элементов:", elements);
                }
            } catch (e) {
                console.error("❌ Ошибка при обработке данных редактируемых элементов:", e);
            }
        }
        
        // Применяем значения к элементам на странице
        applyValuesToElements();
        
        // Инициализируем flatpickr для полей даты, если он есть
        if (typeof flatpickr !== "undefined") {
            initDatePickers();
        } else {
            console.warn("⚠️ flatpickr не найден, функциональность календаря будет ограничена");
        }
        
        console.log("✅ Редактор шаблонов инициализирован успешно");
    }
    
    /**
     * Получение состояния редактируемых элементов
     * @returns {Object} объект состояния
     */
    function getEditableElements() {
        return { 
            elements: state.editableElements,
            unsavedChanges: state.unsavedChanges 
        };
    }
    
    /**
     * Применение значений к элементам на странице
     */
    function applyValuesToElements() {
        const editableElements = document.querySelectorAll(config.editableSelector);
        
        editableElements.forEach(element => {
            const fieldId = element.getAttribute("data-editable");
            const fieldType = element.getAttribute("data-field-type") || "text";
            
            if (state.editableElements && state.editableElements[fieldId]) {
                const storedValue = state.editableElements[fieldId].value;
                
                if (fieldType === "date") {
                    // Для дат применяем форматирование
                    element.textContent = formatDate(storedValue);
                } else {
                    // Для обычного текста просто устанавливаем значение
                    element.textContent = storedValue;
                }
                
                console.log(`✏️ Применено значение для ${fieldId}: ${storedValue}`);
            }
        });
    }
    
    /**
     * Инициализация datepicker для полей с датами
     */
    function initDatePickers() {
        const dateFields = document.querySelectorAll(`${config.editableSelector}${config.dateFieldSelector}`);
        
        dateFields.forEach(element => {
            const fieldId = element.getAttribute("data-editable");
            
            // Настройка flatpickr
            const options = {
                dateFormat: "d.m.Y",
                locale: "ru",
                allowInput: true,
                disableMobile: false,
                onClose: function(selectedDates, dateStr) {
                    if (dateStr) {
                        // Обновляем текст элемента
                        element.textContent = dateStr;
                        
                        // Вызываем обработчик изменений
                        handleFieldChanged(fieldId, dateStr, "date");
                    }
                }
            };
            
            // Инициализируем flatpickr
            try {
                const fpInstance = flatpickr(element, options);
                state.datePickerInstances[fieldId] = fpInstance;
            } catch (error) {
                console.error(`Ошибка при инициализации flatpickr для ${fieldId}:`, error);
            }
        });
    }
    
    /**
     * Обработчик изменения поля
     * @param {string} fieldId - идентификатор поля
     * @param {string} value - новое значение
     * @param {string} fieldType - тип поля
     */
    function handleFieldChanged(fieldId, value, fieldType) {
        console.log(`🔄 Изменение поля ${fieldId}: ${value} (${fieldType})`);
        
        // Обновляем состояние
        if (!state.editableElements) {
            state.editableElements = {};
        }
        
        state.editableElements[fieldId] = {
            value: value,
            type: fieldType
        };
        
        state.unsavedChanges = true;
        
        // Генерируем событие изменения
        const event = new CustomEvent("template-editor:change", {
            detail: {
                fieldName: fieldId,
                value: value,
                fieldType: fieldType
            }
        });
        
        document.dispatchEvent(event);
    }
    
    /**
     * Форматирование даты
     * @param {string} dateString - строка с датой
     * @returns {string} форматированная дата
     */
    function formatDate(dateString) {
        if (!dateString) return "";
        
        try {
            // Проверяем формат входящей даты
            let date;
            
            if (dateString.includes(".")) {
                // Если дата в формате DD.MM.YYYY
                const [day, month, year] = dateString.split(".");
                date = new Date(`${year}-${month}-${day}`);
            } else if (dateString.includes("-")) {
                // Если дата в формате YYYY-MM-DD
                date = new Date(dateString);
            } else {
                // Пробуем распарсить как timestamp
                date = new Date(parseInt(dateString));
            }
            
            // Проверяем валидность даты
            if (isNaN(date)) {
                console.warn(`⚠️ Невалидная дата: ${dateString}`);
                return dateString;
            }
            
            // Форматируем дату в формат DD.MM.YYYY
            const day = date.getDate().toString().padStart(2, "0");
            const month = (date.getMonth() + 1).toString().padStart(2, "0");
            const year = date.getFullYear();
            
            return `${day}.${month}.${year}`;
        } catch (e) {
            console.error(`Ошибка при форматировании даты ${dateString}:`, e);
            return dateString;
        }
    }
    
    /**
     * Конвертация формата даты
     * @param {string} dateString - строка с датой
     * @param {string} fromFormat - исходный формат
     * @param {string} toFormat - целевой формат
     * @returns {string} конвертированная дата
     */
    function convertDateFormat(dateString, fromFormat = "DD.MM.YYYY", toFormat = "YYYY-MM-DD") {
        if (!dateString) return "";
        
        try {
            if (fromFormat === "DD.MM.YYYY" && toFormat === "YYYY-MM-DD") {
                const [day, month, year] = dateString.split(".");
                return `${year}-${month}-${day}`;
            } else if (fromFormat === "YYYY-MM-DD" && toFormat === "DD.MM.YYYY") {
                const [year, month, day] = dateString.split("-");
                return `${day}.${month}.${year}`;
            }
            
            return dateString;
        } catch (e) {
            console.error(`Ошибка при конвертации даты ${dateString}:`, e);
            return dateString;
        }
    }
    
    /**
     * Отключение обработчиков событий для элементов
     * Используется при переключении между редакторами
     */
    function disableElementHandlers() {
        // Удаляем все экземпляры flatpickr
        for (const fieldId in state.datePickerInstances) {
            if (state.datePickerInstances[fieldId] && typeof state.datePickerInstances[fieldId].destroy === "function") {
                state.datePickerInstances[fieldId].destroy();
            }
        }
        
        state.datePickerInstances = {};
        console.log("🚫 Обработчики событий элементов отключены");
    }
    
    /**
     * Метод для сохранения данных шаблона.
     * Перед сохранением открывает модальное окно для настройки серии, если оно существует.
     */
    function saveTemplateData() {
        console.log('Вызов метода сохранения шаблона');
        
        // Проверяем наличие модального окна серии
        const seriesModal = document.getElementById('seriesModal');
        if (seriesModal && typeof bootstrap !== 'undefined') {
            console.log('Открываем модальное окно настройки серии перед сохранением');
            
            // Используем сохраненный экземпляр или создаем новый
            if (window.seriesModalInstance) {
                window.seriesModalInstance.show();
            } else {
                window.seriesModalInstance = new bootstrap.Modal(seriesModal);
                window.seriesModalInstance.show();
            }
        } else {
            console.log('Модальное окно серии не найдено, сохраняем шаблон напрямую');
            
            // Если модальное окно не найдено, вызываем функцию сохранения напрямую
            if (window.saveTemplateData && typeof window.saveTemplateData === 'function') {
                window.saveTemplateData();
            }
        }
    }
    
    // CSS для календаря
    const calendarStyles = `
        .simple-calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .simple-calendar-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 2px;
        }
        
        .simple-calendar-day {
            padding: 5px;
            text-align: center;
            cursor: pointer;
            border-radius: 4px;
        }
        
        .simple-calendar-day:hover {
            background-color: #f0f0f0;
        }
        
        .simple-calendar-day.active {
            background-color: #007bff;
            color: white;
        }
        
        .simple-calendar-day.other-month {
            color: #ccc;
        }
        
        .simple-calendar-weekday {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
        }
    `;
    
    /**
     * Добавление стилей календаря
     */
    function addCalendarStyles() {
        const styleElement = document.createElement("style");
        styleElement.textContent = calendarStyles;
        document.head.appendChild(styleElement);
    }
    
    // Возвращаем публичное API
    return {
        init,
        getEditableElements,
        formatDate,
        convertDateFormat,
        onFieldChanged: handleFieldChanged,
        disableElementHandlers,
        hasUnsavedChanges: function() { return state.unsavedChanges; },
        saveTemplateData
    };
})();

// Экспорт в глобальную область видимости
window.TemplateEditor = TemplateEditor;

// Инициализация немедленно для доступности в других скриптах
(function() {
    try {
        if (typeof TemplateEditor !== "undefined" && TemplateEditor.init) {
            TemplateEditor.init();
            console.log("✅ TemplateEditor инициализирован немедленно");
        }
    } catch (e) {
        console.error("❌ Ошибка при инициализации TemplateEditor:", e);
    }
})();

// Инициализация при загрузке документа
document.addEventListener("DOMContentLoaded", function() {
    if (typeof TemplateEditor !== "undefined" && TemplateEditor.init) {
        TemplateEditor.init();
        console.log("✅ TemplateEditor успешно инициализирован при загрузке страницы");
    } else {
        console.error("❌ TemplateEditor не найден или не имеет метода init");
    }
    
    // Предупреждение о несохраненных изменениях
    window.addEventListener("beforeunload", function(e) {
        if (TemplateEditor && TemplateEditor.hasUnsavedChanges && TemplateEditor.hasUnsavedChanges()) {
            const message = "У вас есть несохраненные изменения. Вы уверены, что хотите покинуть страницу?";
            if (e) {
                e.preventDefault();
                e.returnValue = message;
            }
            return message;
        }
    });
});

