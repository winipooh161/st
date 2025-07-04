export class MobileNavEvents {
    constructor(core, scroll, popup) {
        this.core = core;
        this.scroll = scroll;
        this.popup = popup;
        
        // Состояние
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.isTouchMoved = false;
        this.isLongPress = false;
        this.longPressTimer = null;
        this.longPressDelay = 500; // ms для срабатывания долгого нажатия
        this.activeIconId = null; // Текущая активная иконка для модального окна
        
        // Добавляем обработчик для системных жестов
        this.preventSystemGestures = this.preventSystemGestures.bind(this);
        
        // Инициализация после создания объекта
        this.init();
    }
    
    init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.setupEventListeners();
            });
        } else {
            // DOM уже загружен
            setTimeout(() => this.setupEventListeners(), 500);
        }
    }
    
    setupEventListeners() {
        if (!this.core.isInitialized || !this.core.container) {
            return;
        }

        // Слушаем события открытия/закрытия модальных окон
        this.setupModalListeners();
        
        // События касания на навигации
        this.setupTouchEvents();
        
        // События клика на иконках
        this.setupClickEvents();
        
        // Добавляем защиту от системных жестов
        this.setupSystemGesturesProtection();
    }
    
    setupModalListeners() {
        // Интеграция с унифицированной системой модальных окон
        document.addEventListener('modal.opened', (event) => {
            const modalId = event.detail?.modalId;
            let sourceIconId = event.detail?.sourceIconId;
            
            // Если sourceIconId не определен, пробуем получить его из modalTriggers
            if (!sourceIconId && modalId && this.popup.modalTriggers.has(modalId)) {
                sourceIconId = this.popup.modalTriggers.get(modalId).iconId;
            }
            
            if (modalId && sourceIconId) {
                // Проверяем, нужно ли обновить активную иконку
                if (this.activeIconId !== sourceIconId) {
                    // Восстанавливаем предыдущую иконку если она была
                    if (this.activeIconId) {
                        this.core.restoreIcon(this.activeIconId);
                    }
                    
                    this.activeIconId = sourceIconId;
                    
                    // Преобразуем иконку в кнопку "назад"
                    this.core.convertIconToBackButton(sourceIconId);
                } else {
                    // Переустанавливаем обработчики для уже активной иконки
                    this.core.restoreIcon(sourceIconId);
                    this.core.convertIconToBackButton(sourceIconId);
                }
            }
        });
        
        document.addEventListener('modal.closed', (event) => {
            const modalId = event.detail?.modalId;
            
            if (modalId && this.activeIconId) {
                this.core.restoreIcon(this.activeIconId);
                this.activeIconId = null;
            }
        });
        
        // Связываем с модальной системой, если она существует
        if (window.modalPanel) {
            // Проверяем, не модифицированы ли методы уже
            if (!window.modalPanel._methodsModified) {
                const originalOpenModal = window.modalPanel.openModal;
                const originalCloseModal = window.modalPanel.closeModal;
                
                // Модифицируем метод открытия модального окна
                window.modalPanel.openModal = (modalId) => {
                    const result = originalOpenModal.call(window.modalPanel, modalId);
                    
                    if (result) {
                        // Если есть информация о триггере модального окна
                        let triggerInfo = null;
                        
                        // Проверяем наличие информации в modalSources модальной системы
                        if (window.modalPanel.modalSources && window.modalPanel.modalSources.has(modalId)) {
                            triggerInfo = window.modalPanel.modalSources.get(modalId);
                        } 
                        // Если нет, проверяем в popup.modalTriggers
                        else if (this.popup && this.popup.modalTriggers.has(modalId)) {
                            triggerInfo = this.popup.modalTriggers.get(modalId);
                        }
                        
                        if (triggerInfo && triggerInfo.iconId) {
                            // Создаем и отправляем событие открытия модального окна
                            const event = new CustomEvent('modal.opened', {
                                detail: {
                                    modalId: modalId,
                                    sourceIconId: triggerInfo.iconId
                                }
                            });
                            document.dispatchEvent(event);
                        }
                    }
                    
                    return result;
                };
                
                // Модифицируем метод закрытия модального окна
                window.modalPanel.closeModal = (immediate = false) => {
                    // Получаем ID активного модального окна перед закрытием
                    const modalId = window.modalPanel.activeModal?.id;
                    
                    // Вызываем оригинальный метод
                    originalCloseModal.call(window.modalPanel, immediate);
                    
                    if (modalId) {
                        // Создаем и отправляем событие закрытия модального окна
                        const event = new CustomEvent('modal.closed', {
                            detail: {
                                modalId: modalId
                            }
                        });
                        document.dispatchEvent(event);
                    }
                };
                
                // Отмечаем, что методы уже модифицированы
                window.modalPanel._methodsModified = true;
            }
        }
    }
    
    setupTouchEvents() {
        // Обработка начала касания
        this.core.container.addEventListener('touchstart', (e) => {
            // Сохраняем начальные координаты касания
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
            this.isTouchMoved = false;
            
            // Определяем элемент под пальцем
            const touchedElement = document.elementFromPoint(this.touchStartX, this.touchStartY);
            const iconWrapper = touchedElement ? touchedElement.closest('.mb-icon-wrapper') : null;
            
            if (iconWrapper) {
                // Добавляем визуальный эффект при касании
                iconWrapper.classList.add('mb-touch-active');
                
                // Очищаем существующий таймер долгого нажатия, если есть
                if (this.longPressTimer) {
                    clearTimeout(this.longPressTimer);
                }
                
                // Устанавливаем таймер для долгого нажатия
                this.longPressTimer = setTimeout(() => {
                    if (!this.isTouchMoved) {
                        this.isLongPress = true;
                        this.handleLongPress(iconWrapper);
                        
                        // Сохраняем информацию о взаимодействии в popup
                        if (this.popup) {
                            const iconId = iconWrapper.getAttribute('data-icon-id');
                            this.popup.userHasInteracted = true;
                            this.popup.swipeTargetElement = iconWrapper;
                            this.popup.swipeTargetIconId = iconId;
                        }
                    }
                }, this.longPressDelay);
            }
            
            // Предотвращаем стандартное поведение браузера для предотвращения системных жестов
            // но только для элементов навигации
            if (e.target.closest('.mb-navigation') || e.target.closest('.mobile-nav-component')) {
                e.preventDefault();
            }
        }, { passive: false }); // passive: false для возможности вызвать preventDefault
        
        // Обработка перемещения пальца
        this.core.container.addEventListener('touchmove', (e) => {
            // Определяем элемент под пальцем
            const touchX = e.touches[0].clientX;
            const touchY = e.touches[0].clientY;
            const touchedElement = document.elementFromPoint(touchX, touchY);
            const iconWrapper = touchedElement ? touchedElement.closest('.mb-icon-wrapper') : null;
            
            // Вычисляем дельту движения
            const deltaX = touchX - this.touchStartX;
            const deltaY = touchY - this.touchStartY;
            
            // Проверяем свайп вверх на иконке - обработка делегирована в MobileNavPopup
            if (!this.isTouchMoved && deltaY < -30 && Math.abs(deltaY) > Math.abs(deltaX) && iconWrapper) {
                this.isTouchMoved = true;
                
                // Логика свайпа вверх полностью обрабатывается в MobileNavPopup.setupSwipeDetection()
                // Здесь мы только отмечаем, что касание было сдвинуто
                const iconId = iconWrapper.getAttribute('data-icon-id');
                console.log(`⬆️ Свайп вверх детектирован в Events для иконки ${iconId} - передача в Popup`);
                
                // Очищаем таймер долгого нажатия
                if (this.longPressTimer) {
                    clearTimeout(this.longPressTimer);
                    this.longPressTimer = null;
                }
            }
            // Обычное движение пальца
            else if (this.longPressTimer && (Math.abs(deltaX) > 10 || Math.abs(deltaY) > 10)) {
                this.isTouchMoved = true;
                clearTimeout(this.longPressTimer);
                this.longPressTimer = null;
                
                // Удаляем эффект активного нажатия
                document.querySelectorAll('.mb-touch-active').forEach(el => {
                    el.classList.remove('mb-touch-active');
                });
            }
            
            // Предотвращаем стандартное поведение браузера для элементов навигации
            if (e.target.closest('.mb-navigation') || e.target.closest('.mobile-nav-component')) {
                e.preventDefault();
            }
        }, { passive: false }); // passive: false для возможности вызвать preventDefault
        
        // Обработка завершения касания
        this.core.container.addEventListener('touchend', (e) => {
            // Удаляем эффект активного нажатия
            document.querySelectorAll('.mb-touch-active').forEach(el => {
                el.classList.remove('mb-touch-active');
            });
            
            // Очищаем таймер долгого нажатия
            if (this.longPressTimer) {
                clearTimeout(this.longPressTimer);
                this.longPressTimer = null;
            }
            
            // Если это не было долгое нажатие или свайп, то позволяем выполниться обычному клику
            if (!this.isLongPress && !this.isTouchMoved) {
                const touchEndX = e.changedTouches[0].clientX;
                const touchEndY = e.changedTouches[0].clientY;
                const touchedElement = document.elementFromPoint(touchEndX, touchEndY);
                const iconWrapper = touchedElement ? touchedElement.closest('.mb-icon-wrapper') : null;
                
                if (iconWrapper) {
                    const iconId = iconWrapper.getAttribute('data-icon-id');
                    console.log(`👆 Тап на иконке ${iconId}`);
                    
                    // Проверяем наличие popup для этой иконки только для информации
                    if (iconId && this.popup && this.popup.popupConfigs && this.popup.popupConfigs[iconId]) {
                        console.log(`ℹ️ Для иконки ${iconId} доступно всплывающее меню по свайпу вверх`);
                    }
                    
                    // Проверяем, заблокированы ли клики после свайпа
                    if (this.popup && this.popup.areClicksBlocked && this.popup.areClicksBlocked()) {
                        console.log(`🚫 Клики заблокированы после свайпа, пропускаем обработку клика для ${iconId}`);
                        return;
                    }
                    
                    // Проверяем наличие обычной ссылки и выполняем переход напрямую
                    const link = iconWrapper.querySelector('a[href]');
                    if (link && link.getAttribute('href') !== '#' && link.getAttribute('href') !== 'javascript:void(0);') {
                        const href = link.getAttribute('href');
                        console.log(`🔗 Прямой переход по ссылке для иконки: ${iconId} -> ${href}`);
                        window.location.href = href;
                        return;
                    }
                    
                    // Искусственно вызываем click событие только если нет прямой ссылки
                    setTimeout(() => {
                        const clickEvent = new MouseEvent('click', {
                            bubbles: true,
                            cancelable: true,
                            view: window,
                            clientX: touchEndX,
                            clientY: touchEndY
                        });
                        iconWrapper.dispatchEvent(clickEvent);
                    }, 0);
                }
            }
            
            // Сбрасываем состояние долгого нажатия
            this.isLongPress = false;
            this.isTouchMoved = false;
        }, { passive: true });
    }
    
    setupClickEvents() {
        // Находим все иконки в навигации
        const allIcons = document.querySelectorAll('.mb-icon-wrapper');
        
        // Обрабатываем все иконки, а не только триггеры модальных окон
        allIcons.forEach(icon => {
            const iconId = icon.getAttribute('data-icon-id');
            const modalId = icon.getAttribute('data-modal-target');
            const isModal = icon.getAttribute('data-modal') === 'true';
            
            if (modalId && iconId && isModal) {
                // Добавляем информацию о триггере модального окна в popup
                this.popup.modalTriggers.set(modalId, {
                    element: icon,
                    iconId: iconId
                });
            }
            
            // Добавляем обработчики кликов для всех иконок
            icon.addEventListener('click', (e) => {
                // Если это триггер модального окна
                if (isModal && modalId) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Открываем модальное окно через глобальную функцию
                    if (iconId === 'qr-scanner' && window.openQrScannerModal) {
                        window.openQrScannerModal(icon);
                    } else if (window.modalPanel) {
                        window.modalPanel.openModal(modalId);
                    }
                } 
                // Для иконок со свайпом, проверяем возможность показа попапа
                else if (this.popup.popupConfigs && this.popup.popupConfigs[iconId]) {
                    // Проверяем, активировано ли всплывающее меню по клику (по умолчанию - НЕТ)
                    const popupOnClick = icon.getAttribute('data-popup-on-click') === 'true';
                    
                    if (popupOnClick) {
                        e.preventDefault();
                        this.popup.showPopup(iconId);
                    } else {
                        // Если есть ссылка (href), позволяем браузеру выполнить переход
                        const link = icon.querySelector('a[href]');
                        if (link && link.getAttribute('href') !== '#' && link.getAttribute('href') !== 'javascript:void(0);') {
                            // НЕ вызываем preventDefault(), позволяя стандартному поведению сработать
                            window.location.href = link.getAttribute('href');
                            return;
                        }
                    }
                } 
                // Специальная обработка для кнопки сохранения
                else if (iconId === 'save') {
                    e.preventDefault();
                    
                    // Проверяем тип страницы и вызываем соответствующую функцию
                    if (window.location.href.includes('templates/editor') || 
                        window.location.href.includes('templates/editor')) {
                        // Страница редактора шаблонов
                        if (typeof window.saveTemplateForm === 'function') {
                            window.saveTemplateForm();
                        }
                    } else if (window.location.href.includes('media/editor')) {
                        // Страница медиа редактора
                        if (typeof window.processMedia === 'function') {
                            window.processMedia();
                        }
                    }
                } else {
                    // Если нет popup конфигурации, проверяем наличие ссылки
                    const link = icon.querySelector('a[href]');
                    if (link && link.getAttribute('href') !== '#' && link.getAttribute('href') !== 'javascript:void(0);') {
                        // НЕ вызываем preventDefault(), выполняем переход напрямую
                        window.location.href = link.getAttribute('href');
                        return;
                    }
                }
            });
        });
    }
    
    handleLongPress(iconWrapper) {
        // Получаем ID иконки
        const iconId = iconWrapper.getAttribute('data-icon-id');
        if (!iconId) return;
        
        // Добавляем класс для эффекта долгого нажатия
        iconWrapper.classList.add('mb-long-press');
        
        // Вибрация для тактильной обратной связи
        if (navigator.vibrate) {
            try {
                navigator.vibrate(50);
            } catch (e) {
                // Игнорируем ошибки vibrate API
            }
        }
        
        // Показываем всплывающее меню
        setTimeout(() => {
            this.popup.showPopup(iconId);
            
            // Удаляем эффект долгого нажатия
            iconWrapper.classList.remove('mb-long-press');
        }, 300);
    }
    
    // Метод для защиты от системных жестов
    setupSystemGesturesProtection() {
        if (this.core.container) {
            // Применяем CSS свойство overscroll-behavior для современных браузеров
            document.documentElement.style.overscrollBehaviorX = 'none';
            document.body.style.overscrollBehaviorX = 'none';
            document.documentElement.style.overscrollBehaviorY = 'none';
            document.body.style.overscrollBehaviorY = 'contain';
            this.core.container.style.overscrollBehaviorX = 'none';
            this.core.container.style.touchAction = 'pan-y';
            
            // Дополнительно применяем CSS свойства для iOS Safari
            document.documentElement.style.webkitOverflowScrolling = 'touch';
            document.body.style.webkitOverflowScrolling = 'touch';
            
            // Создаем и добавляем стили для защиты от системных жестов
            const style = document.createElement('style');
            style.innerHTML = `
                .mb-navigation, .mobile-nav-component {
                    touch-action: pan-y;
                    -ms-touch-action: pan-y;
                    -webkit-touch-callout: none;
                    -webkit-user-select: none;
                    -moz-user-select: none;
                    -ms-user-select: none;
                    user-select: none;
                }
                
                .edge-detector {
                    position: fixed;
                    top: 0;
                    height: 100vh;
                    width: 20px;
                    z-index: 9999;
                    background: transparent;
                }
                
                .edge-detector-left {
                    left: 0;
                }
                
                .edge-detector-right {
                    right: 0;
                }
            `;
            document.head.appendChild(style);
            
            // Для улучшенного обнаружения системных жестов используем non-passive listener
            document.addEventListener('touchstart', this.preventSystemGestures, { passive: false });
            document.addEventListener('touchmove', this.preventSystemGestures, { passive: false });
            
            // Для iOS 13+ добавляем div-перехватчики для краев экрана
            if ('ontouchstart' in window) {
                this.addEdgeDetectors();
            }
            
            // Предотвращаем возникновение контекстного меню при долгом тапе
            // на всех элементах навигации
            document.querySelectorAll('.mb-icon-wrapper, .mb-navigation, .mobile-nav-component').forEach(element => {
                element.addEventListener('contextmenu', (e) => {
                    e.preventDefault();
                    return false;
                });
            });
            
            // Добавляем обработчик для навигации назад/вперед браузера
            window.addEventListener('popstate', (e) => {
                // Если открыт popup, закрываем его
                if (this.popup && this.popup.isPopupOpen) {
                    this.popup.hidePopup();
                    e.preventDefault();
                }
            });
            
            // Предотвращаем поведение по умолчанию для жеста "назад" в Safari
            window.addEventListener('popstate', function(e) {
                // Если открыто модальное окно, блокируем навигацию назад
                if (document.querySelector('.modal-panel.show') || 
                    document.querySelector('.mb-popup-container.visible')) {
                    history.pushState(null, null, window.location.pathname);
                    e.preventDefault();
                    return false;
                }
            });
        }
    }
    
    // Добавляем невидимые элементы по бокам экрана для блокировки жестов
    addEdgeDetectors() {
        // Удаляем существующие детекторы, если есть
        document.querySelectorAll('.edge-detector').forEach(detector => {
            detector.remove();
        });
        
        // Создаем левый детектор для жеста "назад"
        const leftDetector = document.createElement('div');
        leftDetector.className = 'edge-detector edge-detector-left';
        document.body.appendChild(leftDetector);
        
        // Создаем правый детектор для жеста "вперед"
        const rightDetector = document.createElement('div');
        rightDetector.className = 'edge-detector edge-detector-right';
        document.body.appendChild(rightDetector);
        
        // Добавляем обработчики событий для этих детекторов
        [leftDetector, rightDetector].forEach(detector => {
            // Используем более надежный способ обработки событий для iOS
            detector.addEventListener('touchstart', e => {
                console.log('Блокировка системного жеста на краю экрана');
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, { passive: false });
            
            detector.addEventListener('touchmove', e => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, { passive: false });
            
            detector.addEventListener('touchend', e => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }, { passive: false });
            
            // Дополнительно для iOS Safari, который может игнорировать preventDefault
            detector.addEventListener('touchstart', this.preventDefaultForEvent, { passive: false });
            detector.addEventListener('touchmove', this.preventDefaultForEvent, { passive: false });
        });
        
        console.log('✅ Детекторы краев экрана добавлены для защиты от системных жестов');
    }
    
    // Улучшенный обработчик для предотвращения жестов браузера
    preventSystemGestures(e) {
        // Получаем текущую позицию касания
        const touchX = e.type === 'touchstart' || e.type === 'touchmove' ? e.touches[0].clientX : 0;
        const touchY = e.type === 'touchstart' || e.type === 'touchmove' ? e.touches[0].clientY : 0;
        const windowWidth = window.innerWidth;

        if (touchX < 40 || touchX > windowWidth - 40) {
            if (e.type === 'touchmove' && e.touches.length === 1) {
                const deltaX = Math.abs(touchX - this._lastTouchX);
                const deltaY = Math.abs(touchY - this._lastTouchY);
                
                // Если движение преимущественно горизонтальное, блокируем его
                if (deltaX > deltaY) {
                    console.log('🚫 Блокировка системного жеста на краю экрана');
                    e.preventDefault();
                    e.stopPropagation();
                }
            } else if (e.type === 'touchstart') {
                // Сбрасываем начальную позицию для touchstart
                this._lastTouchX = touchX;
                this._lastTouchY = e.touches[0].clientY;
                
                // На всякий случай предотвращаем жесты на краю экрана
                e.preventDefault();
            }
        }
    }
    
    // Метод для предотвращения стандартного поведения для определенных событий
    preventDefaultForEvent(e) {
        e.preventDefault();
        e.stopPropagation();
        return false;
    }
}
             
