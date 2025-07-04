export class MobileNavScroll {
    constructor(core) {
        this.core = core;
        this.isScrolling = false;
        this.scrollTimeout = null;
        this.animationFrame = null;
        this.isCentering = false; // Оставляем переменную для совместимости
        this.centeringQueue = []; // Оставляем для совместимости
        this.lastDebounceTime = 0;
        this.debounceThreshold = 150;
        this.lastScrollLeft = 0;
        this.scrollDirection = 0;
        this.debounceTimeout = null;
        
        // Улучшенные переменные для управления скроллом страницы
        this.lastPageScroll = 0;
        this.pageScrollTimeout = null;
        this.pageScrollThreshold = 5; // минимальное расстояние прокрутки для реакции (уменьшено для большей чувствительности)
        this.hideNavigationTimeout = null;
        this.isNavigationHidden = false;
        this.lastUserActionTime = Date.now(); // Время последнего действия пользователя
        this.inactivityTimeout = null; // Таймер бездействия
        this.inactivityThreshold = 2000; // Порог бездействия в миллисекундах (2 секунды)
        
        // Переменные для улучшенного контроля скролла
        this.scrollVelocity = 0;
        this.lastScrollTime = Date.now();
        this.scrollHistory = []; // История последних движений скролла
        this.isScrollingDown = false;
        this.isScrollingUp = false;
        
        // Переменные для touch событий
        this.touchStartY = null;
        this.touchStartTime = null;

        this.inertiaEnabled = true; // Включение инерции для плавного скролла
        this.inertiaFactor = 0.92; // Фактор инерции (1 - без затухания, 0 - мгновенная остановка)
        this.inertiaThreshold = 0.5; // Порог остановки инерции
        this.momentumValue = 0; // Текущее значение импульса
        this.rafId = null; // ID для requestAnimationFrame

        // Добавляем флаг для отслеживания взаимодействия пользователя
        this.userHasInteracted = false;
        
        // Запускаем систему отслеживания взаимодействия
        this.initUserInteractionTracking();

        // В конструкторе добавим свойство для отслеживания времени последнего скролла
        this.lastScrollTime = 0;
        
        // Переносим инициализацию слушателя скролла в отдельный метод, который будет вызван позже
        this.setupScrollTimeTracking();
    }

    // Новый метод для безопасной настройки отслеживания времени скролла
    setupScrollTimeTracking() {
        // Используем DOMContentLoaded для безопасной инициализации
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.setupScrollListener();
            });
        } else {
            // DOM уже загружен, пробуем настроить сейчас или через таймаут
            setTimeout(() => this.setupScrollListener(), 100);
        }
    }

    // Метод для фактического добавления слушателя скролла
    setupScrollListener() {
        // Проверяем, инициализирован ли контейнер
        if (this.core && this.core.container) {
            this.core.container.addEventListener('scroll', () => {
                this.lastScrollTime = Date.now();
            }, { passive: true });
        } else {
            // Если контейнер всё еще не доступен, попробуем позже
            setTimeout(() => this.setupScrollListener(), 500);
        }
    }

    applyInertia(velocity) {
        if (!this.inertiaEnabled || Math.abs(velocity) < this.inertiaThreshold) return;
        
        this.momentumValue = velocity * 15; // Коэффициент для расчета инерции
        
        // Останавливаем предыдущую анимацию инерции, если она запущена
        if (this.rafId) {
            cancelAnimationFrame(this.rafId);
        }
        
        // Функция для анимации инерции
        const animateInertia = () => {
            // Применяем инерцию
            this.core.container.scrollLeft += this.momentumValue;
            
            // Уменьшаем момент с учетом фактора инерции
            this.momentumValue *= this.inertiaFactor;
            
            // Проверяем, нужно ли продолжать инерцию
            if (Math.abs(this.momentumValue) > this.inertiaThreshold) {
                this.rafId = requestAnimationFrame(animateInertia);
            } else {
                // Завершаем инерцию
                this.rafId = null;
            }
        };
        
        // Запускаем анимацию инерции
        this.rafId = requestAnimationFrame(animateInertia);
    }

    // Метод для получения максимального scrollLeft
    getMaxScrollLeft() {
        const containerWidth = this.core.container.offsetWidth;
        const scrollWidth = this.core.iconsContainer.scrollWidth;
        return Math.max(0, scrollWidth - containerWidth);
    }

    // Новый метод для инициализации отслеживания скролла страницы
    setupPageScrollListener() {
        let ticking = false;
        
        // Используем более эффективный подход с requestAnimationFrame
        const handleScroll = () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    this.handlePageScroll();
                    ticking = false;
                });
                ticking = true;
            }
        };
        
        // Основной слушатель скролла
        window.addEventListener('scroll', handleScroll, { passive: true });
        
        // Дополнительные слушатели для лучшего отслеживания на мобильных
        document.addEventListener('touchstart', (e) => {
            this.handleTouchStart(e);
        }, { passive: true });
        
        document.addEventListener('touchmove', (e) => {
            this.handleTouchMove(e);
        }, { passive: true });
        
        document.addEventListener('touchend', () => {
            this.handleTouchEnd();
        }, { passive: true });
        
        // Обработка touch событий для улучшения отзывчивости на мобильных
        this.setupTouchListeners();
    }
      // Обработка touch событий
    setupTouchListeners() {
        let touchStart = null;
        let touchMove = null;
        let touchDirection = null;
        let lastTouchMove = null;
        
        // Throttle функция для ограничения частоты вызовов
        const throttle = (func, limit) => {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        };
        
        document.addEventListener('touchstart', (e) => {
            touchStart = e.touches[0].clientY;
            touchDirection = null;
            lastTouchMove = touchStart;
        }, { passive: true });
        
        document.addEventListener('touchmove', throttle((e) => {
            if (!touchStart) return;
            
            touchMove = e.touches[0].clientY;
            
            if (lastTouchMove !== null) {
                const diff = touchStart - touchMove;
                
                // Определяем направление свайпа только при значительном перемещении
                if (Math.abs(diff) > 15) {
                    // Свайп вниз (скролл вниз страницы)
                    if (diff < 0 && touchDirection !== 'down') {
                        touchDirection = 'down';
                        if (!this.isInExcludedPath()) {
                            this.hideNavigation();
                        }
                    }
                    // Свайп вверх (скролл вверх страницы)  
                    else if (diff > 15 && touchDirection !== 'up') {
                        touchDirection = 'up';
                        this.showNavigation();
                    }
                }
            }
            
            lastTouchMove = touchMove;
        }, 100), { passive: true });
        
        document.addEventListener('touchend', () => {
            touchStart = null;
            touchMove = null;
            touchDirection = null;
            lastTouchMove = null;
        }, { passive: true });
    }
      // Обработка скролла страницы
    handlePageScroll() {
        const navigation = document.querySelector('.mb-navigation');
        if (!navigation) return;
        
        // Обновляем время последнего действия пользователя
        this.lastUserActionTime = Date.now();
        
        const currentScroll = window.pageYOffset || document.documentElement.scrollTop;
        const currentTime = Date.now();
        
        // Очищаем существующие таймауты
        if (this.pageScrollTimeout) {
            clearTimeout(this.pageScrollTimeout);
        }
        
        if (this.inactivityTimeout) {
            clearTimeout(this.inactivityTimeout);
        }
        
        // Проверяем исключенные пути перед скрытием навигации
        if (this.isInExcludedPath()) {
            return;
        }
        
        // Вычисляем изменение скролла и скорость
        const scrollDelta = currentScroll - this.lastPageScroll;
        const timeDelta = currentTime - this.lastScrollTime;
        const scrollSpeed = Math.abs(scrollDelta) / timeDelta;
        
        // Добавляем в историю скролла
        this.scrollHistory.push({
            delta: scrollDelta,
            time: currentTime,
            speed: scrollSpeed
        });
        
        // Ограничиваем историю последними 5 записями
        if (this.scrollHistory.length > 5) {
            this.scrollHistory.shift();
        }
        
        // Вычисляем среднюю скорость и направление
        const avgSpeed = this.scrollHistory.reduce((sum, item) => sum + item.speed, 0) / this.scrollHistory.length;
        const isSignificantScroll = Math.abs(scrollDelta) > this.pageScrollThreshold;
        const isFastScroll = avgSpeed > 0.5; // Быстрый скролл
        
        if (isSignificantScroll) {
            // Скролл вниз - скрываем навигацию
            if (scrollDelta > 0 && currentScroll > 30) {
                this.hideNavigation();
            } 
            // Скролл вверх - показываем навигацию
            else if (scrollDelta < 0) {
                this.showNavigation();
            }
        }
        
        // Устанавливаем таймаут для показа после паузы в прокрутке
        this.pageScrollTimeout = setTimeout(() => {
            // Показываем только если с момента последнего действия прошло достаточно времени
            // или если пользователь остановился в верхней части страницы
            if ((Date.now() - this.lastUserActionTime >= this.inactivityThreshold) || 
                (currentScroll < 100)) {
                this.showNavigation();
            }
        }, this.inactivityThreshold);
        
        // Всегда устанавливаем таймаут бездействия
        this.setupInactivityDetection();
        
        this.lastPageScroll = currentScroll;
        this.lastScrollTime = currentTime;
    }
    
    // Новый метод для настройки обнаружения бездействия
    setupInactivityDetection() {
        if (this.inactivityTimeout) {
            clearTimeout(this.inactivityTimeout);
        }
        
        this.inactivityTimeout = setTimeout(() => {
            if (this.isNavigationHidden) {
                this.showNavigation();
            }
        }, this.inactivityThreshold);
    }
      // Скрытие навигационной панели с проверкой пути
    hideNavigation() {
        const navigation = document.querySelector('.mb-navigation');
        if (!navigation || this.isNavigationHidden) return;

        // Проверяем исключенные пути - на страницах редактора не скрываем навигацию
        if (this.isInExcludedPath()) {
            console.log('🚫 Скрытие навигации пропущено на странице редактора');
            return;
        }
        
        // Добавляем отладочную информацию
        console.log('⬇️ Скрываем навигацию при скролле вниз');
        
        // Применяем RAF для более плавной анимации
        requestAnimationFrame(() => {
            navigation.classList.add('mb-nav-hidden');
            this.isNavigationHidden = true;
            
            // Обновляем время последнего действия пользователя
            this.lastUserActionTime = Date.now();
            
            // Очищаем таймаут показа, если он был установлен
            if (this.hideNavigationTimeout) {
                clearTimeout(this.hideNavigationTimeout);
                this.hideNavigationTimeout = null;
            }
            
            // Устанавливаем детекцию бездействия с небольшой задержкой
            setTimeout(() => {
                this.setupInactivityDetection();
            }, 100);
            
            console.log('✅ Навигационная панель скрыта при скролле вниз');
        });
    }
      // Улучшенный метод для показа навигации
    showNavigation() {
        const navigation = document.querySelector('.mb-navigation');
        if (!navigation || !this.isNavigationHidden) return;
        
        // Добавляем отладочную информацию
        console.log('⬆️ Показываем навигацию при скролле вверх');
        
        // Убираем настроенный таймаут, если он существует
        if (this.hideNavigationTimeout) {
            clearTimeout(this.hideNavigationTimeout);
            this.hideNavigationTimeout = null;
        }
        
        // Тактильная обратная связь при появлении панели (если доступно)
        if (this.canUseVibrateAPI()) {
            navigator.vibrate(5);
        }
        
        // Применяем RAF для более плавной анимации с отложенным добавлением класса
        requestAnimationFrame(() => {
            // Используем setTimeout для добавления небольшой задержки перед анимацией
            setTimeout(() => {
                navigation.classList.remove('mb-nav-hidden');
                this.isNavigationHidden = false;
                
                // Обновляем время последнего действия пользователя
                this.lastUserActionTime = Date.now();
                
                // Очищаем таймаут бездействия, так как мы только что выполнили действие
                if (this.inactivityTimeout) {
                    clearTimeout(this.inactivityTimeout);
                    this.inactivityTimeout = null;
                }
                
                // Запускаем новый таймер для скрытия после периода бездействия
                this.setupInactivityDetection();
                
                console.log('✅ Навигационная панель показана при скролле вверх');
            }, 50);
        });
    }
    
    // Метод для обнаружения активности пользователя и сброса таймеров
    registerUserActivity() {
        this.lastUserActionTime = Date.now();
        
        // Перезапускаем таймер бездействия
        this.setupInactivityDetection();
    }
    
    // Метод для установки обработчиков активности пользователя
    setupUserActivityListeners() {
        // События, которые свидетельствуют об активности пользователя
        const activityEvents = ['touchstart', 'touchmove', 'mousemove', 'click', 'keydown', 'wheel'];
        
        // Устанавливаем обработчики для каждого типа события
        activityEvents.forEach(eventType => {
            document.addEventListener(eventType, () => {
                this.registerUserActivity();
            }, { passive: true });
        });
    }

    /**
     * Метод для определения, доступно ли использование Vibrate API
     */
    canUseVibrateAPI() {
        return navigator.vibrate && this.userHasInteracted && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }
    
    /**
     * Инициализация системы отслеживания взаимодействий
     */
    initUserInteractionTracking() {
        const interactionEvents = ['click', 'touchstart', 'touchmove', 'mousedown', 'keydown'];
        
        const setUserInteracted = () => {
            this.userHasInteracted = true;
            // Удаляем обработчики после первого взаимодействия
            interactionEvents.forEach(event => {
                document.removeEventListener(event, setUserInteracted, { passive: true });
            });
        };
        
        // Добавляем слушатели событий для определения взаимодействия
        interactionEvents.forEach(event => {
            document.addEventListener(event, setUserInteracted, { passive: true });
        });
    }
      /**
     * Блокировка горизонтального скролла (для интеграции с popup системой)
     * Отключена, так как теперь используется фиксированная раскладка 4 иконок
     */
    blockHorizontalScroll() {
        // Метод оставлен для совместимости, но ничего не делает
        // поскольку горизонтальный скролл больше не используется
        console.log('📱 MobileNavScroll: Горизонтальный скролл отключен (фиксированная раскладка)');
    }
    
    /**
     * Разблокировка горизонтального скролла
     * Отключена, так как теперь используется фиксированная раскладка 4 иконок
     */
    unblockHorizontalScroll() {
        // Метод оставлен для совместимости, но ничего не делает
        // поскольку горизонтальный скролл больше не используется
        console.log('📱 MobileNavScroll: Горизонтальный скролл отключен (фиксированная раскладка)');
    }

    // Проверка нахождения в исключенных путях (где нельзя скрывать навигацию)
    isInExcludedPath() {
        const currentPath = window.location.pathname;
        return currentPath.includes('/templates/editor') || 
               currentPath.includes('/client/templates/editor') || 
               currentPath.includes('/client/templates/editor') ||
               currentPath.includes('/admin/');
    }

    // Новые методы для обработки touch событий
    handleTouchStart(e) {
        this.touchStartY = e.touches[0].clientY;
        this.touchStartTime = Date.now();
        this.isScrollingDown = false;
        this.isScrollingUp = false;
    }
    
    handleTouchMove(e) {
        if (!this.touchStartY) return;
        
        const currentY = e.touches[0].clientY;
        const deltaY = this.touchStartY - currentY;
        const currentTime = Date.now();
        
        // Вычисляем скорость движения
        this.scrollVelocity = deltaY / (currentTime - this.touchStartTime);
        
        // Определяем направление с учетом минимального порога
        if (Math.abs(deltaY) > 10) {
            if (deltaY > 0) {
                // Скролл вниз
                this.isScrollingDown = true;
                this.isScrollingUp = false;
                if (!this.isInExcludedPath()) {
                    this.hideNavigation();
                }
            } else {
                // Скролл вверх
                this.isScrollingUp = true;
                this.isScrollingDown = false;
                this.showNavigation();
            }
        }
    }
    
    handleTouchEnd() {
        this.touchStartY = null;
        this.touchStartTime = null;
        this.scrollVelocity = 0;
        
        // Небольшая задержка перед сбросом флагов направления
        setTimeout(() => {
            this.isScrollingDown = false;
            this.isScrollingUp = false;
        }, 100);
    }
}
