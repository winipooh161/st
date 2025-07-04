/**
 * Встроенный редактор для прямого редактирования текста внутри элементов
 * Позволяет редактировать контент напрямую без использования дополнительных полей ввода
 * Дата: 4 июля 2025 г.
 */

// Создаем объект InlineEditor в любом случае
// Если он уже существует, перезапишем его
const InlineEditor = (function() {
    // Конфигурация
    const config = {
        editableSelector: '[data-editable]',
        dateFieldSelector: '[data-field-type="date"]',
        editingClass: 'editing',
        dateFormat: 'DD.MM.YYYY'
    };

    // Состояние редактора
    const state = {
        activeElement: null,
        originalContent: null,
        flatpickrInstances: {}
    };

    /**
     * Инициализация редактора
     */
    function init() {
        initEditableElements();
        initDateElements();
        
        // Слушатель для отмены редактирования по клику вне элемента
        document.addEventListener('click', handleOutsideClick);
        
        // Слушатель для клавиатурных событий
        document.addEventListener('keydown', handleKeydown);

        console.log('🔧 Инициализирован встроенный редактор');
    }

    /**
     * Инициализация обычных редактируемых элементов
     */
    function initEditableElements() {
        const editableElements = document.querySelectorAll(config.editableSelector);
        
        editableElements.forEach(element => {
            if (element.getAttribute('data-field-type') !== 'date') {
                // Добавляем обработчик клика только для текстовых полей
                element.addEventListener('click', handleEditableClick);
            }
        });
    }

    /**
     * Инициализация элементов даты
     */
    function initDateElements() {
        const dateElements = document.querySelectorAll(config.dateFieldSelector);
        
        dateElements.forEach(element => {
            // Отключаем потенциально конфликтующие обработчики из старого редактора
            disableExistingHandlers(element);
            
            // Получаем формат даты из атрибута
            const dateFormat = element.dataset.dateFormat || 'dd.mm.yyyy';
            const formattedDateFormat = dateFormat.toLowerCase().replace('yyyy', 'Y').replace('dd', 'd').replace('mm', 'm');
            
            // Создаем экземпляр flatpickr для каждого элемента даты
            const flatpickrInstance = flatpickr(element, {
                dateFormat: formattedDateFormat,
                // Настройки для работы без промежуточного input
                altInput: false,
                allowInput: false,
                clickOpens: true, 
                static: true, 
                inline: false, // Не встраиваем календарь в элемент
                // Улучшения для UX
                monthSelectorType: 'dropdown',
                locale: 'ru',
                position: 'auto',
                appendTo: document.body, 
                disableMobile: true, // Отключаем мобильный интерфейс для согласованности
                
                onChange: (selectedDates, dateStr, instance) => {
                    if (dateStr && dateStr !== state.originalContent) {
                        // Сохраняем изменения сразу при выборе даты
                        element.textContent = dateStr;
                        notifyChange(element);
                    }
                },
                
                onOpen: () => {
                    // Добавляем класс редактирования при открытии календаря
                    element.classList.add(config.editingClass);
                },
                
                onClose: () => {
                    // Убираем класс редактирования при закрытии календаря
                    element.classList.remove(config.editingClass);
                    // Сбрасываем активный элемент
                    state.activeElement = null;
                }
            });
            
            // Сохраняем экземпляр flatpickr
            state.flatpickrInstances[element.dataset.editable] = flatpickrInstance;
            
            // Отключаем все обработчики, которые могут создавать промежуточные элементы формы
            element.removeAttribute('class');
            element.classList.add('flatpickr-input');
            
            // Заменяем обработчик клика на прямое открытие календаря
            element.addEventListener('click', (event) => {
                // Останавливаем распространение, чтобы предотвратить конфликты с другими обработчиками
                event.preventDefault();
                event.stopPropagation();
                
                // Удаляем все возможные промежуточные элементы формы рядом с этим элементом
                const parent = element.parentNode;
                const siblings = Array.from(parent.children);
                siblings.forEach(sibling => {
                    if (sibling !== element && (sibling.classList.contains('form-control') || 
                        sibling.classList.contains('editing') || 
                        sibling.hasAttribute('readonly'))) {
                        parent.removeChild(sibling);
                    }
                });
                
                // Если у нас есть активный элемент текстового типа, выходим из его редактирования
                if (state.activeElement && state.activeElement !== element) {
                    exitEditMode(state.activeElement);
                }
                
                // Сохраняем исходное содержимое
                state.originalContent = element.textContent.trim();
                state.activeElement = element;
                
                // Добавляем класс редактирования
                element.classList.add(config.editingClass);
                
                // Создаем и открываем календарь при клике
                setTimeout(() => {
                    try {
                        // Проверяем инициализацию flatpickr для этого элемента
                        if (!flatpickrInstance) {
                            // Если flatpickr не инициализирован, создаем его
                            console.log('Создаем новый экземпляр календаря для элемента');
                            
                            // Настраиваем параметры flatpickr
                            const options = {
                                dateFormat: 'd.m.Y',
                                locale: 'ru',
                                allowInput: true,
                                disableMobile: false,
                                onClose: function(selectedDates, dateStr) {
                                    if (dateStr && element) {
                                        element.textContent = dateStr;
                                        exitEditMode(element);
                                    }
                                }
                            };
                            
                            // Создаем экземпляр flatpickr
                            flatpickrInstance = flatpickr(element, options);
                            state.flatpickrInstances[element.getAttribute('data-editable')] = flatpickrInstance;
                        }
                        
                        // Теперь безопасно открываем календарь
                        if (flatpickrInstance && typeof flatpickrInstance.open === 'function') {
                            // Проверяем, что flatpickrInstance не имеет свойства disabled или оно false
                            if (!flatpickrInstance.disabled) {
                                flatpickrInstance.open();
                                
                                // Перемещаем календарь в видимую область
                                const calendar = document.querySelector('.flatpickr-calendar');
                                if (calendar) {
                                    ensureElementVisible(calendar);
                                }
                            } else {
                                console.warn(`⚠️ Календарь отключен для элемента: ${element.textContent}`);
                            }
                        } else {
                            console.warn(`⚠️ Не удалось открыть календарь для элемента: ${element.textContent}`);
                        }
                    } catch (error) {
                        console.error('Ошибка при работе с календарем:', error);
                    }
                }, 50);
            });
        });
    }

    /**
     * Обработчик клика по редактируемому элементу
     * @param {Event} event - событие клика
     */
    function handleEditableClick(event) {
        const element = event.currentTarget;
        
        // Если мы уже редактируем этот элемент, то ничего не делаем
        if (element === state.activeElement) return;
        
        // Если у нас есть активный элемент, выходим из режима редактирования
        if (state.activeElement) {
            exitEditMode(state.activeElement);
        }
        
        // Сохраняем исходное содержимое
        state.originalContent = element.textContent.trim();
        state.activeElement = element;
        
        // Входим в режим редактирования
        enterEditMode(element);
        
        // Останавливаем всплытие события
        event.stopPropagation();
    }

    /**
     * Обработчик клика по элементу даты (устарел и не используется, т.к. логика перенесена в initDateElements)
     * @deprecated
     * @param {Event} event - событие клика
     */
    function handleDateClick(event) {
        // Функционал перенесен напрямую в обработчик в initDateElements
    }

    /**
     * Обработчик клика вне редактируемого элемента
     * @param {Event} event - событие клика
     */
    function handleOutsideClick(event) {
        if (!state.activeElement) return;
        
        // Проверяем, что клик был не по активному элементу и не по его потомкам
        if (state.activeElement !== event.target && !state.activeElement.contains(event.target)) {
            // Проверяем, что клик не был по flatpickr календарю
            if (!event.target.closest('.flatpickr-calendar')) {
                exitEditMode(state.activeElement);
            }
        }
    }

    /**
     * Обработчик клавиатурных событий
     * @param {Event} event - событие нажатия клавиши
     */
    function handleKeydown(event) {
        if (!state.activeElement) return;
        
        // Если нажата клавиша Escape, отменяем редактирование
        if (event.key === 'Escape') {
            cancelEdit(state.activeElement);
            event.preventDefault();
        }
        
        // Если нажата клавиша Enter, сохраняем изменения
        if (event.key === 'Enter' && !event.shiftKey) {
            exitEditMode(state.activeElement);
            event.preventDefault();
        }
    }

    /**
     * Вход в режим редактирования
     * @param {HTMLElement} element - редактируемый элемент
     */
    function enterEditMode(element) {
        // Добавляем класс редактирования
        element.classList.add(config.editingClass);
        
        // Делаем элемент редактируемым
        element.contentEditable = 'true';
        
        // Фокусируемся на элементе и устанавливаем курсор в конец текста
        element.focus();
        
        // Устанавливаем курсор в конец текста
        const range = document.createRange();
        const selection = window.getSelection();
        range.selectNodeContents(element);
        range.collapse(false); // false означает, что коллапс происходит к концу узла
        selection.removeAllRanges();
        selection.addRange(range);
    }

    /**
     * Выход из режима редактирования с сохранением изменений
     * @param {HTMLElement} element - редактируемый элемент
     */
    function exitEditMode(element) {
        // Убираем класс редактирования и делаем элемент не редактируемым
        element.classList.remove(config.editingClass);
        element.contentEditable = 'false';
        
        // Сохраняем изменения если они есть
        const newContent = element.textContent.trim();
        if (newContent !== state.originalContent) {
            notifyChange(element);
        }
        
        // Сбрасываем активный элемент
        state.activeElement = null;
    }

    /**
     * Отмена редактирования без сохранения изменений
     * @param {HTMLElement} element - редактируемый элемент
     */
    function cancelEdit(element) {
        // Возвращаем исходное содержимое
        element.textContent = state.originalContent;
        
        // Выходим из режима редактирования
        exitEditMode(element);
    }

    /**
     * Уведомление о изменении контента
     * @param {HTMLElement} element - редактируемый элемент
     */
    function notifyChange(element) {
        const fieldId = element.dataset.editable;
        const fieldName = element.dataset.fieldName || fieldId;
        const fieldType = element.dataset.fieldType || 'text';
        const newValue = element.textContent.trim();
        
        console.log(`📝 Изменено поле ${fieldName}: ${newValue}`);
        
        // Вызываем событие изменения данных
        const changeEvent = new CustomEvent('template:fieldChanged', {
            detail: {
                fieldId,
                fieldName,
                fieldType,
                value: newValue
            }
        });
        
        document.dispatchEvent(changeEvent);
        
        // Если есть глобальный объект TemplateEditor, уведомляем его
        if (window.TemplateEditor && window.TemplateEditor.onFieldChanged) {
            window.TemplateEditor.onFieldChanged(fieldId, newValue, fieldType);
        }
        
        // Обновляем данные в TemplateDataCollector если он доступен
        if (window.TemplateDataCollector && window.TemplateDataCollector.updateField) {
            window.TemplateDataCollector.updateField(fieldId, newValue, fieldType);
        }
        
        // Устанавливаем флаг несохраненных изменений
        if (window.TemplateDataCollector) {
            window.TemplateDataCollector.setUnsavedChanges(true);
        }
    }

    /**
     * Обновление всех редактируемых элементов после динамического добавления контента
     */
    function refreshEditableElements() {
        // Очищаем существующие экземпляры flatpickr
        Object.values(state.flatpickrInstances).forEach(instance => {
            instance.destroy();
        });
        state.flatpickrInstances = {};
        
        // Повторная инициализация
        initEditableElements();
        initDateElements();
    }

    /**
     * Обеспечивает, чтобы элемент был виден на экране (прокручивает при необходимости)
     * @param {HTMLElement} element - элемент для проверки видимости
     */
    function ensureElementVisible(element) {
        const rect = element.getBoundingClientRect();
        const windowHeight = window.innerHeight || document.documentElement.clientHeight;
        
        // Если календарь выходит за нижнюю границу экрана
        if (rect.bottom > windowHeight) {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            window.scrollTo({
                top: scrollTop + (rect.bottom - windowHeight) + 20, // +20px для отступа
                behavior: 'smooth'
            });
        }
        
        // Если календарь выходит за верхнюю границу экрана
        if (rect.top < 0) {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            window.scrollTo({
                top: scrollTop + rect.top - 20, // -20px для отступа
                behavior: 'smooth'
            });
        }
    }

    /**
     * Отключает существующие обработчики событий, которые могут конфликтовать с нашим редактором
     * @param {HTMLElement} element - элемент с обработчиками
     */
    function disableExistingHandlers(element) {
        // Проверяем, есть ли у элемента data-initialized атрибут
        if (element.hasAttribute('data-initialized-inline-editor')) {
            return; // Уже инициализирован нашим редактором
        }
        
        // Если элемент уже инициализирован другим редактором (TemplateEditor), отключаем его обработчики
        if (window.TemplateEditor && window.TemplateEditor.disableElementHandlers) {
            window.TemplateEditor.disableElementHandlers(element);
        }
        
        // Помечаем элемент как инициализированный нашим редактором
        element.setAttribute('data-initialized-inline-editor', 'true');
        
        // Предотвращаем повторное создание календаря в других обработчиках
        if (element.getAttribute('data-field-type') === 'date') {
            // Отключаем contentEditable для элементов даты, чтобы предотвратить двойное редактирование
            element.setAttribute('data-inline-edit-only', 'true');
        }
    }

    // Публичный API
    return {
        init,
        refreshEditableElements,
        exitEditMode: function() {
            if (state.activeElement) {
                exitEditMode(state.activeElement);
            }
        }
    };
})();

// Экспорт в глобальную область видимости
window.InlineEditor = InlineEditor;

// Инициализация немедленно для доступности в других скриптах
(function() {
    try {
        if (typeof InlineEditor !== 'undefined' && InlineEditor.init) {
            InlineEditor.init();
            console.log('✅ InlineEditor инициализирован немедленно');
        }
    } catch (e) {
        console.error('❌ Ошибка при инициализации InlineEditor:', e);
    }
})();

// Инициализация при загрузке страницы
document.addEventListener('DOMContentLoaded', function() {
    if (typeof InlineEditor !== 'undefined' && InlineEditor.init) {
        console.log('📝 Инициализация InlineEditor при загрузке страницы...');
        InlineEditor.init();
        console.log('✅ InlineEditor успешно инициализирован');
    } else {
        console.error('❌ InlineEditor не найден или не имеет метода init');
    }
});


