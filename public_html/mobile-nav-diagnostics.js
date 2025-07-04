// Файл мобильной навигационной диагностики
console.log('🔍 Диагностика мобильной навигации запущена');

// Проверка основных объектов
document.addEventListener('DOMContentLoaded', () => {
    runDiagnostics();
});

// Основная функция диагностики
function runDiagnostics() {
    // Проверка наличия объекта MobileNavWheelPicker
    if (window.MobileNavWheelPicker) {
        console.log('✅ Объект MobileNavWheelPicker инициализирован');
    } else {
        console.log('❌ Объект MobileNavWheelPicker не инициализирован');
    }

    // Проверка загрузки скриптов
    checkScriptLoading();
    
    // Проверка DOM элементов
    checkDOMElements();
    
    // Проверка событий
    monitorEvents();
    
    // Проверка CSS стилей
    checkStyles();
    
    // Отслеживание ошибок
    setupErrorTracking();
}

// Проверка загрузки скриптов
function checkScriptLoading() {
    const scripts = document.querySelectorAll('script');
    console.log(`📋 Загружено ${scripts.length} скриптов`);
    
    // Проверяем наличие нужных скриптов
    const scriptURLs = Array.from(scripts).map(s => s.src);
    
    const requiredScripts = [
        '/js/mobile-nav-wheel-picker.js',
        '/js/mobile-nav/index.js'
    ];
    
    requiredScripts.forEach(script => {
        const isLoaded = scriptURLs.some(url => url.includes(script));
        console.log(`${isLoaded ? '✅' : '❌'} Скрипт ${script} ${isLoaded ? 'загружен' : 'не загружен'}`);
    });
}

// Проверка DOM элементов
function checkDOMElements() {
    // Проверка основного контейнера
    const navContainer = document.querySelector('.mobile-nav-container');
    if (navContainer) {
        console.log('✅ Контейнер мобильной навигации найден');
    } else {
        console.log('❌ Контейнер мобильной навигации не найден');
    }
    
    // Проверка базовых кнопок
    const triggers = document.querySelectorAll('[data-mobile-nav-trigger]');
    console.log(`📋 Найдено ${triggers.length} триггеров мобильной навигации`);
}

// Проверка CSS стилей
function checkStyles() {
    // Получаем все применяемые стили
    const allStyles = Array.from(document.styleSheets);
    let navStylesFound = false;
    
    console.log(`📋 Загружено ${allStyles.length} таблиц стилей`);
    
    // Ищем в стилях классы с "mobile-nav"
    allStyles.forEach((styleSheet) => {
        try {
            const rules = Array.from(styleSheet.cssRules);
            rules.forEach((rule) => {
                if (rule.selectorText && rule.selectorText.includes('mobile-nav')) {
                    navStylesFound = true;
                }
            });
        } catch (e) {
            // Пропускаем стили из других доменов
            console.log('⚠️ Не удалось прочитать стили из: ' + styleSheet.href);
        }
    });
    
    console.log(`${navStylesFound ? '✅' : '❌'} Стили мобильной навигации ${navStylesFound ? 'найдены' : 'не найдены'}`);
}

// Отслеживание ошибок
function setupErrorTracking() {
    console.log('🔍 Настройка мониторинга ошибок мобильной навигации');
    
    window.onerror = function(message, source, lineno, colno, error) {
        if(source.includes('mobile-nav') || message.includes('MobileNav')) {
            console.error(`❌ Ошибка в мобильной навигации: ${message} (${source}:${lineno}:${colno})`);
            console.error('Стек вызовов:', error ? error.stack : 'Недоступен');
        }
    };
}

// Мониторинг событий
function monitorEvents() {
    console.log('🔍 Настройка мониторинга событий мобильной навигации');
    
    // Отслеживаем клики по документу
    document.addEventListener('click', function(e) {
        const target = e.target.closest('[data-mobile-nav-trigger]');
        if (target) {
            console.log('🖱️ Клик по триггеру мобильной навигации:', target);
        }
    });
    
    // Отслеживаем изменения размера окна
    window.addEventListener('resize', debounce(function() {
        console.log('📏 Изменение размера окна - проверка мобильной навигации');
        checkViewportSize();
    }, 250));
    
    // Сразу проверяем размер окна
    checkViewportSize();
}

// Проверка размера экрана
function checkViewportSize() {
    const width = window.innerWidth;
    const height = window.innerHeight;
    console.log(`📱 Размер viewport: ${width}x${height}px`);
    
    if (width <= 768) {
        console.log('📱 Мобильный режим активен');
    } else {
        console.log('🖥️ Десктопный режим активен');
    }
}

// Вспомогательная функция debounce
function debounce(func, wait) {
    let timeout;
    return function() {
        const context = this;
        const args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => {
            func.apply(context, args);
        }, wait);
    };
}

// Интервал для периодической проверки
setInterval(() => {
    // Периодически проверяем инициализацию объекта
    if (window.MobileNavWheelPicker) {
        console.log('✅ Периодическая проверка: MobileNavWheelPicker инициализирован');
    } else {
        console.log('❌ Периодическая проверка: MobileNavWheelPicker не инициализирован');
        
        // Проверка наличия основных скриптов
        const scripts = document.querySelectorAll('script');
        const scriptURLs = Array.from(scripts).map(s => s.src);
        
        const mainScript = scriptURLs.find(url => url.includes('/js/mobile-nav/index.js'));
        if (mainScript) {
            console.log('⚠️ Скрипт мобильной навигации загружен, но объект не инициализирован');
            
            // Проверка консольных ошибок
            console.log('🔍 Проверьте консоль на наличие ошибок JavaScript');
        }
    }
}, 10000); // Проверка каждые 10 секунд

// Проверить есть ли в исходном коде MobileNavWheelPicker вызов console.error
console.log('🔍 Проверка наличия ошибок в коде MobileNavWheelPicker');

// Проверка на ошибки синтаксиса в связанных файлах
try {
    // Это тестовая функция, которая будет пытаться загрузить и выполнить код через eval
    // Обычно eval не рекомендуется, но для диагностики это допустимо
    function testScriptSyntax(url) {
        return fetch(url)
            .then(response => response.text())
            .then(code => {
                try {
                    // Проверяем только синтаксис, не выполняем
                    new Function(code);
                    console.log(`✅ Синтаксис файла ${url} корректен`);
                    return true;
                } catch (e) {
                    console.error(`❌ Синтаксическая ошибка в файле ${url}:`, e.message);
                    return false;
                }
            })
            .catch(err => {
                console.error(`❌ Не удалось загрузить файл ${url}:`, err);
                return false;
            });
    }
    
    // Проверяем файлы мобильной навигации
    Promise.all([
        testScriptSyntax('/js/mobile-nav/index.js'),
        testScriptSyntax('/js/mobile-nav-wheel-picker.js')
    ]).then(results => {
        const allValid = results.every(Boolean);
        console.log(`${allValid ? '✅' : '❌'} Проверка синтаксиса файлов: ${allValid ? 'все файлы корректны' : 'обнаружены ошибки'}`);
    });
} catch (e) {
    console.error('❌ Ошибка при проверке синтаксиса файлов:', e);
}
