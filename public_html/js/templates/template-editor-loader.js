/**
 * Загрузчик редактора шаблонов
 * Отвечает за подключение всех необходимых скриптов и стилей
 * Дата: 4 июля 2025 г.
 */

(function() {
    // Конфигурация
    const config = {
        // Пути к файлам скриптов
        scripts: [
            '/js/templates/template-editor.js',
            '/js/templates/template-data-collector.js'
        ],
        // Пути к файлам стилей
        styles: [
            '/css/templates/template-direct-editor.css'
        ],
        // Зависимости для календаря (опционально)
        dependencies: {
            flatpickr: {
                script: 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.js',
                style: 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/flatpickr.min.css',
                localeScript: 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.9/dist/l10n/ru.js'
            }
        },
        // DOM-селектор для контейнера шаблона
        templateContainer: '#template-content'
    };

    /**
     * Загрузка скрипта
     * @param {string} src - путь к скрипту
     * @returns {Promise} промис, разрешающийся после загрузки скрипта
     */
    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }
            
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }
    
    /**
     * Загрузка стиля
     * @param {string} href - путь к CSS-файлу
     * @returns {Promise} промис, разрешающийся после загрузки стиля
     */
    function loadStyle(href) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`link[href="${href}"]`)) {
                resolve();
                return;
            }
            
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.onload = resolve;
            link.onerror = reject;
            document.head.appendChild(link);
        });
    }
    
    /**
     * Проверка наличия элемента шаблона
     * @returns {boolean} true если элемент существует, иначе false
     */
    function checkTemplateContainer() {
        return !!document.querySelector(config.templateContainer);
    }
    
    /**
     * Инициализация загрузчика
     */
    async function init() {
        console.log('Инициализация загрузчика редактора шаблонов...');
        
        try {
            // Проверяем наличие контейнера шаблона
            if (!checkTemplateContainer()) {
                console.warn(`Контейнер шаблона ${config.templateContainer} не найден. Редактор не будет инициализирован.`);
                return;
            }
            
            // Загружаем стили
            const stylePromises = config.styles.map(loadStyle);
            await Promise.all(stylePromises);
            console.log('✅ Стили загружены успешно');
            
            // Загружаем flatpickr, если он не загружен
            if (!window.flatpickr) {
                try {
                    await loadStyle(config.dependencies.flatpickr.style);
                    await loadScript(config.dependencies.flatpickr.script);
                    await loadScript(config.dependencies.flatpickr.localeScript);
                    console.log('✅ Flatpickr загружен успешно');
                } catch (error) {
                    console.warn('⚠️ Ошибка загрузки Flatpickr, будет использован встроенный календарь:', error);
                }
            }
            
            // Загружаем основные скрипты
            const scriptPromises = config.scripts.map(loadScript);
            await Promise.all(scriptPromises);
            console.log('✅ Скрипты загружены успешно');
            
            // Ожидаем инициализации редактора и сборщика данных
            await new Promise(resolve => {
                const checkInit = () => {
                    if (window.TemplateEditor && window.TemplateDataCollector) {
                        resolve();
                    } else {
                        setTimeout(checkInit, 50);
                    }
                };
                checkInit();
            });
            
            console.log('🚀 Загрузчик редактора шаблонов инициализирован успешно');
            
            // Добавляем класс к контейнеру для обозначения готовности редактора
            document.querySelector(config.templateContainer).classList.add('template-editor-ready');
            
        } catch (error) {
            console.error('❌ Ошибка инициализации загрузчика редактора шаблонов:', error);
        }
    }
    
    // Инициализируем загрузчик при загрузке документа
    document.addEventListener('DOMContentLoaded', init);
})();
