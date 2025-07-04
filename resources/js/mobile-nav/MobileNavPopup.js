export class MobileNavPopup {
    constructor(core) {
        this.core = core;
        this.isPopupOpen = false;
        this.currentPopupConfig = null;
        this.popupContainer = null;
        this.backdrop = null;
        this.swipeStartY = 0;
        this.swipeStartX = 0;
        this.isSwipeDetected = false;
        this.minSwipeDistance = 50;
        this.isUpSwipeInProgress = false;
        
        // Теперь вместо предопределенных конфигураций будем загружать из HTML
        this.popupConfigs = {};
        
        // Добавляем флаг для отслеживания взаимодействия пользователя
        this.userHasInteracted = false;
        
        // Добавляем новые переменные для отслеживания иконки свайпа
        this.swipeTargetElement = null;
        this.swipeTargetIconId = null;
        
        // Добавляем переменные для хранения модальных триггеров
        this.modalTriggers = new Map();        // Добавляем переменные для управления скроллом
        this.originalBodyOverflow = '';
        this.originalBodyPosition = '';
        this.scrollY = 0;

        // Добавляем переменную для отслеживания состояния блокировки кликов
        this.isClicksBlocked = false;

        // Привязываем методы к контексту для безопасности
        this.checkAndShowHintIfNeeded = this.checkAndShowHintIfNeeded.bind(this);
        this.showSwipeHint = this.showSwipeHint.bind(this);

        this.init();
    }

    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.delayedInit();
            });
        } else {
            this.delayedInit();
        }
    }    delayedInit() {
        setTimeout(() => {
            console.log('🎪 MobileNavPopup: Начинаем отложенную инициализацию...');
            
            try {
                this.createPopupElements();
                console.log('✅ Popup элементы созданы');
                
                this.loadPopupConfigsFromHtml();
                console.log('✅ Конфигурации загружены');
                
                this.setupSwipeDetection();
                console.log('✅ Обнаружение свайпов настроено');
                
                // Инициализируем интеграцию с модальной системой
                this.initModalSystemIntegration();
                console.log('✅ Интеграция с модальной системой настроена');
                
                console.log('🎪 MobileNavPopup: Инициализация завершена успешно');
                
                // Проверяем результат инициализации
                this.validateInitialization();
                
            } catch (error) {
                console.error('❌ Ошибка при инициализации MobileNavPopup:', error);
                
                // Пробуем повторную инициализацию через 1 секунду
                setTimeout(() => {
                    console.log('🔄 Попытка повторной инициализации...');
                    this.delayedInit();
                }, 1000);
            }
        }, 500);
    }
    
    // Новый метод для проверки корректности инициализации
    validateInitialization() {
        const issues = [];
        
        if (!this.popupContainer) {
            issues.push('Popup контейнер не создан');
        }
        
        if (!this.backdrop) {
            issues.push('Backdrop не создан');
        }
        
        if (!this.popupConfigs || Object.keys(this.popupConfigs).length === 0) {
            issues.push('Конфигурации не загружены');
        }
        
        if (!this.core.container) {
            issues.push('Контейнер навигации не найден');
        }
        
        if (issues.length > 0) {
            console.warn('⚠️ Обнаружены проблемы при инициализации:', issues);
            return false;
        }
        
        console.log('✅ Валидация инициализации прошла успешно');
        return true;
    }
    
    // Новый метод для загрузки конфигурации из HTML
    loadPopupConfigsFromHtml() {
        console.log('🔍 Начинаем загрузку конфигураций popup из HTML...');
        const popupConfigElement = document.getElementById('mobile-nav-popup-configs');
        
        // Если элемент с конфигурациями не найден, используем запасной вариант
        if (!popupConfigElement) {
            console.warn('❌ Элемент #mobile-nav-popup-configs не найден, используются запасные конфигурации');
            this.popupConfigs = this.getFallbackPopupConfigs();
            console.log('📋 Загружены запасные конфигурации меню:', Object.keys(this.popupConfigs));
            return;
        }
        
        console.log('✅ Элемент #mobile-nav-popup-configs найден');
        console.log('📄 HTML содержимое элемента:', popupConfigElement.innerHTML.substring(0, 200) + '...');
        
        // Найти все секции конфигураций по атрибуту data-popup-config
        const configSections = popupConfigElement.querySelectorAll('[data-popup-config]');
        console.log(`🔍 Найдено ${configSections.length} секций конфигураций`);
        
        if (configSections.length === 0) {
            console.warn('❌ Секции конфигурации не найдены внутри #mobile-nav-popup-configs, используются запасные конфигурации');
            this.popupConfigs = this.getFallbackPopupConfigs();
            return;
        }
        
        configSections.forEach(section => {
            const configId = section.getAttribute('data-popup-config');
            console.log(`📝 Обрабатываем конфигурацию: ${configId}`);
            
            // Поддерживаем две структуры: новую (.popup-header) и старую (.popup-config-title)
            const headerElement = section.querySelector('.popup-header, .popup-config-title');
            const title = headerElement ? headerElement.textContent.trim() : configId;
            
            // Поддерживаем поиск элементов как напрямую в секции, так и в .popup-items
            const itemElements = section.querySelectorAll('.popup-item');
            console.log(`  └─ Найдено ${itemElements.length} элементов меню`);
            
            const items = [];
            
            itemElements.forEach((item, index) => {
                const icon = item.getAttribute('data-icon');
                const href = item.getAttribute('data-href') || '#';
                const title = item.getAttribute('data-title');
                const isModal = item.getAttribute('data-modal') === 'true';
                const modalId = item.getAttribute('data-modal-target');
                
                console.log(`    └─ Элемент ${index + 1}: ${title} (${isModal ? 'модальное' : 'ссылка'})`);
                
                items.push({
                    icon: icon,
                    href: href,
                    title: title,
                    isModal: isModal,
                    modalId: modalId
                });
            });
            
            // Сохраняем конфигурацию для этого ID
            if (items.length > 0) {
                this.popupConfigs[configId] = {
                    title: title,
                    items: items
                };
                console.log(`✅ Загружена конфигурация меню для "${configId}" с ${items.length} элементами`);
            } else {
                console.warn(`⚠️ Пустая конфигурация для меню "${configId}"`);
            }
        });
        
        // Если ничего не загружено, используем запасной вариант
        if (Object.keys(this.popupConfigs).length === 0) {
            console.warn('❌ Не загружено ни одной конфигурации, используются запасные конфигурации');
            this.popupConfigs = this.getFallbackPopupConfigs();
        }
        
        console.log('🎯 Финальные загруженные конфигурации:', Object.keys(this.popupConfigs));
    }

    // Запасной вариант конфигурации, если HTML не найден
    getFallbackPopupConfigs() {
        return {
            'home': {
                title: 'Главная',
                items: [
                    { icon: 'newspaper.svg', href: '/news', isModal: false, title: 'Новости' },
                    { icon: 'calendar.svg', href: '/events', isModal: false, title: 'События' },
                    { icon: 'info-circle.svg', href: '/about', isModal: false, title: 'О нас' }
                ]
            },
            'profile': {
                title: 'Профиль',
                items: [
                    { icon: 'gear.svg', href: '/profile/settings', isModal: false, title: 'Настройки' },
                    { icon: 'clock-history.svg', href: '/user/templates', isModal: false, title: 'История' },
                    { icon: 'heart.svg', href: '/user/favorites', isModal: false, title: 'Избранное' }
                ]
            },
            'create': {
                title: 'Создать',
                items: [
                    { icon: 'folder-plus.svg', href: '/client/projects', isModal: false, title: 'Проекты' },
                    { icon: 'image.svg', href: '/client/images', isModal: false, title: 'Изображения' }
                ]
            },
            'games': {
                title: 'Развлечения',
                items: [
                    { icon: 'puzzle.svg', href: '/games/puzzle', isModal: false, title: 'Пазлы' },
                    { icon: 'controller.svg', href: '/games/arcade', isModal: false, title: 'Аркады' },
                    { icon: 'trophy.svg', href: '/games/tournaments', isModal: false, title: 'Турниры' }
                ]
            },
            'email': {
                title: 'Почта',
                items: [
                    { icon: 'inbox.svg', href: '/email/inbox', isModal: false, title: 'Входящие' },
                    { icon: 'send.svg', href: '/email/sent', isModal: false, title: 'Отправленные' },
                    { icon: 'pencil.svg', href: '/email/compose', isModal: false, title: 'Написать' }
                ]
            },
            'admin': {
                title: 'Администрирование',
                items: [
                    { icon: 'people.svg', href: '/admin/users', isModal: false, title: 'Пользователи' },
                    { icon: 'bar-chart.svg', href: '/admin/statistics', isModal: false, title: 'Статистика' },
                    { icon: 'gear.svg', href: '/admin/settings', isModal: false, title: 'Настройки' }
                ]
            },
            'qr-scanner': {
                title: 'QR Сканер',
                items: [
                    { icon: 'qr-code.svg', href: '#', isModal: true, modalId: 'qr-scanner-modal', title: 'Сканировать' },
                    { icon: 'camera.svg', href: '#', isModal: true, modalId: 'camera-modal', title: 'Камера' },
                    { icon: 'image.svg', href: '/qr/history', isModal: false, title: 'История' }
                ]
            }
        };
    }

    createPopupElements() {
        console.log('🏗️ Создаем элементы popup...');
        
        this.backdrop = document.createElement('div');
        this.backdrop.className = 'mb-popup-backdrop';
        console.log('✅ Backdrop создан:', this.backdrop);
        
        this.popupContainer = document.createElement('div');
        this.popupContainer.className = 'mb-popup-container mb-popup-swipeable';
        console.log('✅ Popup контейнер создан:', this.popupContainer);

        // Добавляем индикатор свайпа
        const swipeIndicator = document.createElement('div');
        swipeIndicator.className = 'mb-swipe-indicator';
        this.popupContainer.appendChild(swipeIndicator);

        document.body.appendChild(this.backdrop);
        document.body.appendChild(this.popupContainer);
        
        console.log('✅ Элементы добавлены в DOM');
        console.log('🔍 Backdrop в DOM:', document.body.contains(this.backdrop));
        console.log('🔍 Popup в DOM:', document.body.contains(this.popupContainer));

        this.backdrop.addEventListener('click', () => {
            console.log('👆 Клик по backdrop, закрываем popup');
            this.closePopup();
        });
        
        this.popupContainer.addEventListener('touchstart', (e) => {
            this.swipeStartY = e.touches[0].clientY;
        });

        this.popupContainer.addEventListener('touchend', (e) => {
            const swipeEndY = e.changedTouches[0].clientY;
            const swipeDistance = swipeEndY - this.swipeStartY;
            
            if (swipeDistance > this.minSwipeDistance) {
                console.log('👆 Свайп вниз обнаружен, закрываем popup');
                this.closePopup();
            }
        });
        
        // Добавляем CSS стили для popup
        this.ensureAnimationStyles();
        
        console.log('🎪 MobileNavPopup: Элементы popup созданы и настроены');
    }

    setupSwipeDetection() {
        if (!this.core.container) {
            const container = document.getElementById('nav-scroll-container');
            if (container) {
                this.core.container = container;
            } else {
                return;
            }
        }

        this.core.container.addEventListener('touchstart', (e) => {
            // Устанавливаем флаг взаимодействия пользователя
            this.userHasInteracted = true;
            
            // Вместо проверки только центрированного элемента,
            // определяем, над какой иконкой находится палец
            const touch = e.touches[0];
            const touchX = touch.clientX;
            const touchY = touch.clientY;
            
            // Получаем все иконки в контейнере
            const allIcons = Array.from(this.core.iconsContainer.querySelectorAll('.mb-icon-wrapper'));
            
            // Находим иконку под пальцем пользователя
            let targetIcon = null;
            for (const icon of allIcons) {
                const rect = icon.getBoundingClientRect();
                if (
                    touchX >= rect.left && 
                    touchX <= rect.right && 
                    touchY >= rect.top && 
                    touchY <= rect.bottom
                ) {
                    targetIcon = icon;
                    break;
                }
            }
            
            // Если иконка найдена, сохраняем ее данные
            if (targetIcon) {
                this.swipeTargetElement = targetIcon;
                this.swipeTargetIconId = targetIcon.getAttribute('data-icon-id');
                this.swipeStartY = touchY;
                this.swipeStartX = touchX;
                this.isSwipeDetected = false;
                this.isUpSwipeInProgress = false;
                
                // Проверяем, есть ли popup для этой иконки и показываем подсказку при необходимости
                if (this.swipeTargetIconId && this.popupConfigs[this.swipeTargetIconId]) {
                    // Сохраняем ссылку на текущий контекст для использования в setTimeout
                    const currentPopupInstance = this;
                    const currentTargetElement = this.swipeTargetElement;
                    
                    setTimeout(() => {
                        if (currentPopupInstance && typeof currentPopupInstance.checkAndShowHintIfNeeded === 'function') {
                            currentPopupInstance.checkAndShowHintIfNeeded(currentTargetElement);
                        } else {
                            console.warn('⚠️ Метод checkAndShowHintIfNeeded не найден в MobileNavPopup или контекст потерян');
                        }
                    }, 0);
                }
                
                // Добавляем визуальную обратную связь при касании
                this.swipeTargetElement.classList.add('mb-touch-active');
            } else {
                // Сбрасываем данные, если касание не попало на иконку
                this.swipeTargetElement = null;
                this.swipeTargetIconId = null;
                this.swipeStartY = 0;
                this.swipeStartX = 0;
            }
        }, { passive: true });        this.core.container.addEventListener('touchmove', (e) => {
            // Если нет активной иконки для свайпа, выходим
            if (!this.swipeTargetElement || this.swipeStartY === 0) return;

            const touch = e.touches[0];
            const deltaY = this.swipeStartY - touch.clientY;
            const deltaX = Math.abs(touch.clientX - this.swipeStartX);

            // Улучшенное распознавание свайпа вверх
            if (deltaY > 15 && deltaX < 75) { // Увеличиваем допуск по X для лучшего UX
                this.isUpSwipeInProgress = true;
                
                // Добавляем подробный лог для диагностики
                console.log(`📏 Свайп обнаружен: ΔY=${deltaY.toFixed(1)}, ΔX=${deltaX.toFixed(1)}, IconID=${this.swipeTargetIconId}`);
                
                // Показываем визуальную подсказку о свайпе вверх
                if (!this.swipeTargetElement.classList.contains('swiping-up')) {
                    this.swipeTargetElement.classList.add('swiping-up');
                    console.log('⬆️ Добавлен класс swiping-up для визуальной обратной связи');
                }
                  // Блокируем скролл контейнера при свайпе вверх
                if (deltaY > 20) {
                    // Предотвращаем горизонтальный скролл навигации
                    if (this.core.container) {
                        this.core.container.style.overflowX = 'hidden';
                    }
                    
                    // Предотвращаем стандартное поведение браузера
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                }
                
                // Если свайп достаточно длинный, отмечаем его как обнаруженный
                if (deltaY > this.minSwipeDistance) {
                    this.isSwipeDetected = true;
                    
                    // Вибрация для тактильной обратной связи (только один раз)
                    if (!this.swipeTargetElement.hasAttribute('data-vibrated') &&
                        navigator.vibrate && this.userHasInteracted && 
                        !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        try {
                            navigator.vibrate(20);
                            this.swipeTargetElement.setAttribute('data-vibrated', 'true');
                        } catch (error) {
                            // Игнорируем ошибки vibrate API
                        }
                    }
                }
            } else if (deltaX > 30) {
                // Если пользователь делает горизонтальный свайп, отменяем вертикальный
                this.isUpSwipeInProgress = false;
                this.isSwipeDetected = false;
                this.swipeTargetElement.classList.remove('swiping-up');
            }
        }, { passive: false });        this.core.container.addEventListener('touchend', (e) => {
            // Восстанавливаем скролл контейнера
            if (this.core.container) {
                this.core.container.style.overflowX = 'auto';
            }
            
            // Убираем классы активности и визуальные эффекты
            if (this.swipeTargetElement) {
                this.swipeTargetElement.classList.remove('mb-touch-active', 'swiping-up');
                this.swipeTargetElement.removeAttribute('data-vibrated');
            }            // Если был обнаружен свайп вверх и есть целевая иконка
            if (this.isSwipeDetected && this.swipeTargetIconId) {
                console.log(`🔺 Popup: Свайп вверх обнаружен на иконке: ${this.swipeTargetIconId}`);
                
                // ВАЖНО: Предотвращаем клик по иконке после свайпа
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                // ЗАЩИТА: Проверяем, нет ли открытого модального окна
                const hasOpenModal = document.querySelector('.modal.show') !== null;
                if (hasOpenModal) {
                    console.log('🚫 Popup: Модальное окно открыто, пропускаем показ popup');
                    this.resetSwipeState();
                    return;
                }
                
                // Блокируем любые клики на элементе в течение короткого времени
                if (this.swipeTargetElement) {
                    this.preventClicksOnElement(this.swipeTargetElement);
                }
                
                // Показываем попап для конкретной иконки
                this.showPopup(this.swipeTargetIconId);
            }
            
            // Полный сброс состояния
            this.resetSwipeState();
        });
    }
    
    /**
     * Показывает подсказку пользователю о том, как использовать всплывающие меню
     */
    showSwipeHint(iconElement) {
        // Проверяем, показывали ли уже подсказку пользователю
        const hasSeenHint = localStorage.getItem('mobile-nav-swipe-hint-seen');
        if (hasSeenHint) return;
        
        // Создаем элемент подсказки, если его нет
        let hintElement = document.querySelector('.mb-swipe-hint');
        if (!hintElement) {
            hintElement = document.createElement('div');
            hintElement.className = 'mb-swipe-hint';
            hintElement.innerHTML = '⬆️ Проведите вверх для открытия меню';
            document.body.appendChild(hintElement);
        }
        
        // Показываем подсказку
        hintElement.classList.add('show');
        
        // Скрываем подсказку через 3 секунды
        setTimeout(() => {
            hintElement.classList.remove('show');
            // Запоминаем, что пользователь видел подсказку
            localStorage.setItem('mobile-nav-swipe-hint-seen', 'true');
        }, 3000);
    }
    
    /**
     * Проверяет, нужно ли показать подсказку при первом взаимодействии
     */
    checkAndShowHintIfNeeded(iconElement) {
        const hasSeenHint = localStorage.getItem('mobile-nav-swipe-hint-seen');
        const hasUserInteracted = localStorage.getItem('mobile-nav-user-interacted');
        
        // Показываем подсказку только если пользователь еще не видел ее 
        // и это его первое взаимодействие с навигацией
        if (!hasSeenHint && !hasUserInteracted) {
            localStorage.setItem('mobile-nav-user-interacted', 'true');
            
            // Показываем подсказку с небольшой задержкой
            setTimeout(() => {
                this.showSwipeHint(iconElement);
            }, 1000);
        }
    }
    
    /**
     * Сброс состояния свайпа
     */
    resetSwipeState() {
        this.swipeTargetElement = null;
        this.swipeStartY = 0;
        this.swipeStartX = 0;
        this.isSwipeDetected = false;
        this.isUpSwipeInProgress = false;
        this.swipeTargetIconId = null;
    }

    getCenteredItem() {
        // Функция оставлена для совместимости
        return null;
    }    showPopup(iconId) {
        console.log('🔺 MobileNavPopup: Попытка показать popup для иконки:', iconId);
        console.log('🔍 Доступные конфигурации:', Object.keys(this.popupConfigs));
        
        const config = this.popupConfigs[iconId];
        if (!config) {
            console.error('❌ Конфигурация для иконки не найдена:', iconId);
            return;
        }
        
        if (this.isPopupOpen) {
            console.warn('⚠️ Popup уже открыт, пропускаем');
            return;
        }

        // ЗАЩИТА: Проверяем, нет ли открытого модального окна
        const hasOpenModal = document.querySelector('.modal.show') !== null;
        if (hasOpenModal) {
            console.log('🚫 Popup: Модальное окно открыто, не показываем popup для', iconId);
            return;
        }

        console.log('✅ Показываем popup для иконки:', iconId);
        console.log('📋 Конфигурация popup:', config);

        this.currentPopupConfig = config;
        this.isPopupOpen = true;
        
        // Блокируем скролл body перед показом попапа
        this.blockBodyScroll();
        
        // Сохраняем ID иконки, на которой был сделан свайп
        this.currentIconId = iconId;

        // Добавляем заголовок иконки к popup
        this.renderPopupContent(config, iconId);

        // Принудительно показываем элементы
        console.log('🎭 Применяем стили видимости...');
        
        this.backdrop.classList.add('show', 'visible');
        this.popupContainer.classList.add('show', 'visible');
        
        requestAnimationFrame(() => {
            this.backdrop.style.opacity = '1';
            this.backdrop.style.visibility = 'visible';
            
            this.popupContainer.style.opacity = '1';
            this.popupContainer.style.visibility = 'visible';
            this.popupContainer.style.transform = 'translateX(-50%) translateY(0)';
            
            console.log('🎭 Стили применены. Popup должен быть виден.');
            console.log('🔍 Backdrop стили:', {
                opacity: this.backdrop.style.opacity,
                visibility: this.backdrop.style.visibility,
                classList: Array.from(this.backdrop.classList)
            });
            console.log('🔍 Popup стили:', {
                opacity: this.popupContainer.style.opacity,
                visibility: this.popupContainer.style.visibility,
                transform: this.popupContainer.style.transform,
                classList: Array.from(this.popupContainer.classList)
            });
        });

        // Вызываем вибрацию только если есть доступ к API
        if (navigator.vibrate && this.userHasInteracted && 
            !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            try {
                navigator.vibrate(50);
                console.log('📳 Вибрация активирована');
            } catch (error) {
                console.log('❌ Ошибка вибрации:', error);
            }
        }
    }

    renderPopupContent(config, iconId) {
        // Добавляем класс, соответствующий иконке
        this.popupContainer.className = 'mb-popup-container mb-popup-swipeable';
        this.popupContainer.classList.add(`popup-for-${iconId}`);
        
        // Формируем HTML с заголовком иконки
        const iconTitle = this.getIconTitle(iconId);
        
        this.popupContainer.innerHTML = `
            <div class="mb-swipe-indicator"></div>
           
            <div class="mb-popup-grid">
                ${config.items.map((item, index) => {
                    // Определяем атрибуты в зависимости от типа элемента (модальное окно или ссылка)
                    const actionAttrs = item.isModal 
                        ? `href="javascript:void(0);" data-modal="true" data-modal-target="${item.modalId}" class="mb-popup-item modal-trigger no-spinner"`
                        : `href="${item.href}" class="mb-popup-item"`;
                    
                    return `
                        <a ${actionAttrs} style="animation-delay: ${index * 0.1}s;">
                            <img src="/images/icons/${item.icon}" 
                                alt="${item.title}" 
                                title="${item.title}"
                                onerror="this.src='/images/icons/placeholder.svg'; this.classList.add('fallback-icon');"
                                onload="this.classList.add('loaded-icon');">
                            <span class="popup-item-title">${item.title}</span>
                        </a>
                    `;
                }).join('')}
            </div>
        `;

        this.ensureAnimationStyles();
        this.setupPopupEventListeners();
    }
    
    // Новый метод для получения заголовка иконки
    getIconTitle(iconId) {
        // Сначала проверяем конфигурацию попапа
        if (this.popupConfigs[iconId] && this.popupConfigs[iconId].title) {
            return this.popupConfigs[iconId].title;
        }
        
        // Если заголовок не найден в конфигурации, ищем в DOM
        const iconElement = this.core.iconsContainer.querySelector(`[data-icon-id="${iconId}"]`);
        if (iconElement) {
            // Пытаемся найти подпись под иконкой или alt у изображения
            const imgElement = iconElement.querySelector('img');
            if (imgElement && imgElement.alt) {
                return imgElement.alt;
            }
            
            // Проверяем атрибут title у ссылки
            const linkElement = iconElement.querySelector('a');
            if (linkElement && linkElement.title) {
                return linkElement.title;
            }
        }
        
        // Если всё еще не нашли заголовок, используем ID с первой заглавной буквой
        if (iconId) {
            return iconId.charAt(0).toUpperCase() + iconId.slice(1);
        }
        
        return '';
    }    setupPopupEventListeners() {
        // Кнопки закрытия больше нет, поэтому добавляем закрытие по тапу за пределами сетки
        this.popupContainer.addEventListener('click', (e) => {
            // Если клик был вне сетки элементов (.mb-popup-grid), закрываем попап
            if (!e.target.closest('.mb-popup-grid')) {
                e.preventDefault();
                this.closePopup();
            }
        });

        const popupItems = this.popupContainer.querySelectorAll('.mb-popup-item');
        
        popupItems.forEach((item) => {
            // Если это модальное окно
            if (item.hasAttribute('data-modal-target')) {
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const modalId = item.getAttribute('data-modal-target');
                    
                    console.log('🎯 Popup: Клик по модальному триггеру', modalId, 'из иконки', this.currentIconId);
                    
                    // Запоминаем связь между модальным окном и иконкой
                    if (this.currentIconId) {
                        this.modalTriggers.set(modalId, {
                            element: this.swipeTargetElement,
                            iconId: this.currentIconId
                        });
                    }
                    
                    // Сначала закрываем popup
                    this.closePopup();
                    
                    // Небольшая задержка перед открытием модального окна для плавности
                    setTimeout(() => {
                        if (window.openModalPanel) {
                            console.log('🚀 Popup: Открываем модальное окно', modalId);
                            window.openModalPanel(modalId);
                        } else {
                            console.error('openModalPanel не доступен');
                        }
                    }, 200);
                });
            } else {
                // Для обычных ссылок просто закрываем попап
                item.addEventListener('click', () => {
                    this.closePopup();
                });
            }
        });
        
        // Добавляем обработчик для закрытия popup при свайпе вниз
        this.popupContainer.addEventListener('touchstart', (e) => {
            this.swipeStartY = e.touches[0].clientY;
        }, { passive: true });
        
        this.popupContainer.addEventListener('touchmove', (e) => {
            if (this.swipeStartY === 0) return;
            
            const deltaY = e.touches[0].clientY - this.swipeStartY;
            
            // Если свайп вниз больше 50px
            if (deltaY > 50) {
                this.closePopup();
            }
        }, { passive: true });
    }

    ensureAnimationStyles() {
        if (!document.querySelector('#mb-popup-animations')) {
            const style = document.createElement('style');
            style.id = 'mb-popup-animations';
            style.textContent = `
                .mb-popup-item {
                      transform: translateY(20px);
    opacity: 0;
    animation: slideUpFade 0.3s ease forwards;
    display: flex;
    width: 100%;
    flex-direction: row;
    align-items: center;
    gap: 5px;
    align-content: center;
    justify-content: flex-start;
                }
                .popup-item-title {
                   font-size: 16px;
    text-align: center;
    color: #333;
    margin-top: 0;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
                }
                @keyframes slideUpFade {
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
                .mb-popup-item .fallback-icon {
                    opacity: 0.4;
                    filter: grayscale(1);
                }
                .mb-popup-item .loaded-icon {
                    opacity: 1;
                    filter: none;
                }
                .mb-popup-item img {
                    transition: all 0.3s ease;
                    width: 32px;
                    height: 32px;
                }
                
                /* Стили для класса mb-touch-active и swiping-up */
                .mb-icon-wrapper.mb-touch-active {
                    opacity: 0.8;
                    transform: scale(0.95);
                    transition: all 0.2s ease;
                }
                
                .mb-icon-wrapper.swiping-up {
                    transform: scale(0.92);
                }
                
                .mb-icon-wrapper.swiping-up::after {
                    content: '';
                    position: absolute;
                    top: -8px;
                    left: 50%;
                    transform: translateX(-50%);
                    width: 0;
                    height: 0;
                    border-left: 6px solid transparent;
                    border-right: 6px solid transparent;
                    border-bottom: 8px solid rgba(0, 123, 255, 0.6);
                    animation: pulse 1s infinite;
                }
                
                @keyframes pulse {
                    0% { opacity: 0.4; }
                    50% { opacity: 1; }
                    100% { opacity: 0.4; }
                }
                
                /* Стили для заголовка попапа */
                .mb-popup-title {
                    margin: 0;
                    padding: 12px 20px 0;
                    text-align: center;
                    font-weight: 600;
                    font-size: 1rem;
                    color: #333;
                }
                
                /* Стили для кнопки "назад" */
                .mb-icon-wrapper.back-button-active {
                    position: relative;
                    transform: scale(1.05);
                    transition: all 0.3s ease;
                }
                
                .mb-icon-wrapper.back-button-active::after {
                    content: '';
                    position: absolute;
                    bottom: -8px;
                    left: 50%;
                    width: 6px;
                    height: 6px;
                    background-color: #007bff;
                    border-radius: 50%;
                    transform: translateX(-50%);
                    animation: pulse 1.5s infinite;
                }
                
                .mb-icon-wrapper.back-button-active .mb-nav-icon {
                    filter: brightness(1.2);
                    animation: backButtonPulse 2s infinite;
                }
                
                @keyframes backButtonPulse {
                    0%, 100% { transform: scale(1); }
                    50% { transform: scale(1.1); }
                }
            `;
            document.head.appendChild(style);
        }
    }    closePopup() {
        if (!this.isPopupOpen) return;

        console.log('🔽 Popup: Закрываем popup для иконки', this.currentIconId);

        this.backdrop.style.opacity = '0';
        this.backdrop.style.visibility = 'hidden';
        
        this.popupContainer.style.opacity = '0';
        this.popupContainer.style.transform = 'translateX(-50%) translateY(100px)';

        setTimeout(() => {
            this.popupContainer.style.visibility = 'hidden';
            this.isPopupOpen = false;
            this.currentPopupConfig = null;
            
            // Проверяем, нет ли открытых модальных окон
            const hasOpenModal = document.querySelector('.modal-panel.show');
            
            if (!hasOpenModal && this.currentIconId) {
                // Восстанавливаем иконку только если нет открытых модальных окон
                console.log('🔄 Popup: Восстанавливаем иконку', this.currentIconId);
                if (window.MobileNavWheelPicker && window.MobileNavWheelPicker.core) {
                    window.MobileNavWheelPicker.core.restoreIcon(this.currentIconId);
                }
                this.currentIconId = null;
            } else if (hasOpenModal) {
                console.log('🔒 Popup: Модальное окно открыто, оставляем иконку как есть');
                // Не восстанавливаем иконку, если модальное окно открыто
                // currentIconId сохраняем для возможного восстановления при закрытии модального окна
            }
            
            // Разблокируем скролл body после закрытия попапа
            this.unblockBodyScroll();
            
            // Сбрасываем состояние свайпа
            this.swipeTargetElement = null;
            this.swipeTargetIconId = null;
            this.swipeStartY = 0;
            
        }, 400);
    }

    // Метод для интеграции с модальной системой
    initModalSystemIntegration() {        // Закрываем popup при открытии модального окна
        document.addEventListener('modal.opened', (event) => {
            const modalId = event.detail?.modalId;
            console.log('🎭 Popup: Получено событие modal.opened для', modalId);
            
            // ВАЖНО: Сбрасываем состояние свайпа при открытии модального окна
            this.resetSwipeState();
            console.log('🔄 Popup: Состояние свайпа сброшено при открытии модального окна');
            
            if (this.isPopupOpen) {
                console.log('🔽 Popup: Закрываем popup из-за открытия модального окна');
                // Закрываем popup но не восстанавливаем иконку
                this.closePopupForModal();
            }
        });
          // Восстанавливаем иконку при закрытии модального окна
        document.addEventListener('modal.closed', (event) => {
            const modalId = event.detail?.modalId;
            console.log('🎭 Popup: Получено событие modal.closed для', modalId);
            
            // ВАЖНО: Сбрасываем состояние свайпа при закрытии модального окна
            // чтобы предотвратить повторное открытие popup
            this.resetSwipeState();
            console.log('🔄 Popup: Состояние свайпа сброшено при закрытии модального окна');
            
            // Если у нас есть сохраненная иконка для этого модального окна
            if (this.modalTriggers.has(modalId)) {
                const trigger = this.modalTriggers.get(modalId);
                console.log('🔄 Popup: Восстанавливаем иконку из modal.closed', trigger.iconId);
                
                if (window.MobileNavWheelPicker && window.MobileNavWheelPicker.core) {
                    window.MobileNavWheelPicker.core.restoreIcon(trigger.iconId);
                }
                  // Очищаем запись о триггере
                this.modalTriggers.delete(modalId);
                this.currentIconId = null;
            }
        });

        // Дополнительная защита: обработчик afterClose для полного сброса состояния
        document.addEventListener('modal.afterClose', (event) => {
            const modalId = event.detail?.modalId;
            console.log('🎭 Popup: Получено событие modal.afterClose для', modalId);
            
            // Дополнительный сброс состояния свайпа для надежности
            this.resetSwipeState();
            console.log('🔄 Popup: Дополнительный сброс состояния свайпа в modal.afterClose');
        });
    }
      // Специальный метод закрытия popup для модальных окон
    closePopupForModal() {
        if (!this.isPopupOpen) return;

        console.log('🔽 Popup: Закрываем popup для модального окна');

        this.backdrop.style.opacity = '0';
        this.backdrop.style.visibility = 'hidden';
        
        this.popupContainer.style.opacity = '0';
        this.popupContainer.style.transform = 'translateX(-50%) translateY(100px)';        setTimeout(() => {
            this.popupContainer.style.visibility = 'hidden';
            this.isPopupOpen = false;
            this.currentPopupConfig = null;
            
            // НЕ восстанавливаем иконку - оставляем для модальной системы
            
            // Разблокируем скролл body
            this.unblockBodyScroll();
            
            // ВАЖНО: Полный сброс состояния свайпа
            this.resetSwipeState();
            console.log('🔄 Popup: Состояние свайпа сброшено в closePopupForModal');
        }, 200);
    }

    // Улучшенный метод для блокировки скролла body с проверкой текущего пути
    blockBodyScroll() {
        // Проверяем путь - на страницах редактора не блокируем скролл
        const currentPath = window.location.pathname;
        if (currentPath.includes('/templates/editor') || 
            currentPath.includes('/client/templates/editor')) {
            console.log('Блокировка скролла пропущена на странице редактора');
            return;
        }
        
        // Сохраняем текущее положение скролла
        this.scrollY = window.pageYOffset || document.documentElement.scrollTop;
        
        // Сохраняем оригинальные стили body
        this.originalBodyOverflow = document.body.style.overflow;
        this.originalBodyPosition = document.body.style.position;
        
        // Применяем стили для блокировки скролла
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${this.scrollY}px`;
        document.body.style.width = '100%';
        
        // Добавляем класс для дополнительной стилизации
        document.body.classList.add('popup-scroll-blocked');
    }

    // Улучшенный метод для разблокировки скролла body
    unblockBodyScroll() {
        // Проверяем, был ли скролл заблокирован
        if (!document.body.classList.contains('popup-scroll-blocked')) {
            return;
        }
        
        // Восстанавливаем оригинальные стили
        document.body.style.overflow = this.originalBodyOverflow;
        document.body.style.position = this.originalBodyPosition;
        document.body.style.top = '';
        document.body.style.width = '';
        
        // Убираем класс
        document.body.classList.remove('popup-scroll-blocked');
        
        // Восстанавливаем позицию скролла
        if (this.scrollY > 0) {
            window.scrollTo(0, this.scrollY);
        }
        
        // Сбрасываем сохраненные значения
        this.scrollY = 0;
        this.originalBodyOverflow = '';
        this.originalBodyPosition = '';
    }

    // Метод для интеграции с событиями скролла навигации
    blockHorizontalScroll() {
        return this.isUpSwipeInProgress;
    }    /**
     * Временно блокирует клики на элементе после свайпа
     */
    preventClicksOnElement(element) {
        if (!element) return;
        
        console.log('🚫 Popup: Блокируем клики на элементе после свайпа');
        
        // Устанавливаем глобальную блокировку кликов
        this.isClicksBlocked = true;
        
        const clickBlocker = (e) => {
            console.log('🚫 Popup: Заблокирован клик после свайпа');
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            return false;
        };
        
        // Находим все кликабельные элементы внутри иконки
        const clickableElements = [
            element,
            ...element.querySelectorAll('a, button, [data-modal], .modal-trigger')
        ];
        
        // Добавляем блокировщик кликов на все элементы
        clickableElements.forEach(el => {
            el.addEventListener('click', clickBlocker, { capture: true, once: false });
            el.addEventListener('touchend', clickBlocker, { capture: true, once: false });
            el.addEventListener('mouseup', clickBlocker, { capture: true, once: false });
        });
        
        // Убираем блокировщик через короткое время
        setTimeout(() => {
            clickableElements.forEach(el => {
                el.removeEventListener('click', clickBlocker, { capture: true });
                el.removeEventListener('touchend', clickBlocker, { capture: true });
                el.removeEventListener('mouseup', clickBlocker, { capture: true });
            });
            
            // Снимаем глобальную блокировку
            this.isClicksBlocked = false;
            console.log('🔓 Popup: Блокировка кликов снята');
        }, 500); // Увеличиваем время блокировки
    }
    
    /**
     * Проверяет, заблокированы ли клики в данный момент
     */
    areClicksBlocked() {
        return this.isClicksBlocked;
    }
}

// Добавляем глобальную функцию для тестирования popup из консоли
if (typeof window !== 'undefined') {
    window.testMobileNavPopup = function(iconId = 'home') {
        console.log('🧪 Тестируем MobileNavPopup для иконки:', iconId);
        
        const navigation = window.MobileNavWheelPicker;
        if (!navigation) {
            console.error('❌ MobileNavWheelPicker не найден в window');
            return;
        }
        
        if (!navigation.popup) {
            console.error('❌ popup не найден в navigation');
            return;
        }
        
        console.log('📋 Доступные конфигурации:', Object.keys(navigation.popup.popupConfigs));
        console.log('🔍 Состояние popup открыт:', navigation.popup.isPopupOpen);
        console.log('🔍 Popup элементы созданы:', {
            backdrop: !!navigation.popup.backdrop,
            container: !!navigation.popup.popupContainer
        });
        
        if (navigation.popup.popupConfigs[iconId]) {
            console.log('✅ Конфигурация найдена, показываем popup');
            navigation.popup.showPopup(iconId);
        } else {
            console.error('❌ Конфигурация не найдена для иконки:', iconId);
            console.log('💡 Попробуйте:', Object.keys(navigation.popup.popupConfigs));
        }
    };
    
    console.log('🧪 Глобальная функция testMobileNavPopup() добавлена');
    console.log('💡 Использование: testMobileNavPopup("home")');
}
