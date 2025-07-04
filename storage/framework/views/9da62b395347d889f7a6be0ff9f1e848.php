<!-- Улучшенная JavaScript система для работы с модальными окнами -->
<script>
/**
 * Унифицированная система управления модальными окнами
 * Обеспечивает централизованное управление открытием/закрытием модальных окон
 */
class ModalPanelSystem {
    constructor() {
        // Основные свойства
        this.activeModal = null;
        this.backdrop = document.getElementById('modal-backdrop');
        this.modalSources = new Map();
        this.modalQueue = [];
        
        // Управление состоянием
        this.scrollBlocked = false;
        this.isClosing = false;
        this.isOpening = false;
        this.lastActionTime = 0;
        this.debounceDelay = 300;
        
        // События и обратные вызовы
        this.eventListeners = new Map();
        this.beforeOpenCallbacks = new Map();
        this.afterCloseCallbacks = new Map();
        
        // Интеграция с мобильной навигацией
        this.mobileNavIntegration = null;
        
        this.init();
    }    
    init() {
        // Настройка базовых обработчиков
        this.setupEventListeners();
        
        // Интеграция с мобильной навигацией
        this.setupMobileNavIntegration();
        
        // Установка глобальных обработчиков
        this.setupGlobalHandlers();
        
        // Инициализация системы предотвращения конфликтов
        this.initConflictPrevention();
        
        console.log('ModalPanelSystem: Система модальных окон инициализирована');
    }
    
    /**
     * Настройка интеграции с мобильной навигацией
     */
    setupMobileNavIntegration() {
        // Ожидаем инициализации мобильной навигации
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => this.initMobileNavIntegration(), 100);
            });
        } else {
            setTimeout(() => this.initMobileNavIntegration(), 100);
        }
    }
    
    initMobileNavIntegration() {
        // Подключаемся к системе мобильной навигации если она доступна
        if (window.MobileNavWheelPicker) {
            this.mobileNavIntegration = window.MobileNavWheelPicker;
            
            // Настраиваем взаимодействие с popup системой мобильной навигации
            if (this.mobileNavIntegration.popup) {
                this.setupPopupIntegration();
            }
        }
        
        // Находим все элементы с модальными триггерами в мобильной навигации
        this.scanForModalTriggers();
    }
    
    /**
     * Сканирование и регистрация модальных триггеров
     */
    scanForModalTriggers() {
        // Находим все элементы с атрибутами data-modal-target
        const modalTriggers = document.querySelectorAll('[data-modal-target]');
        
        modalTriggers.forEach(trigger => {
            const modalId = trigger.getAttribute('data-modal-target');
            const iconWrapper = trigger.closest('.mb-icon-wrapper');
            
            if (modalId && iconWrapper) {
                const iconId = iconWrapper.getAttribute('data-icon-id');
                
                // Регистрируем источник модального окна
                this.registerModalSource(modalId, {
                    iconId: iconId,
                    element: iconWrapper,
                    trigger: trigger
                });
                
                // Настраиваем обработчики для конкретного типа модального окна
                this.setupSpecificModalHandlers(modalId, trigger, iconWrapper);
            }
        });
    }
    
    /**
     * Регистрация источника модального окна
     */
    registerModalSource(modalId, sourceData) {
        this.modalSources.set(modalId, sourceData);
        
        // Логирование для отладки
        console.log(`ModalPanelSystem: Зарегистрирован источник для ${modalId}:`, sourceData);
    }
    
    /**
     * Настройка специфичных обработчиков для разных типов модальных окон
     */
    setupSpecificModalHandlers(modalId, trigger, iconWrapper) {
        const iconId = iconWrapper.getAttribute('data-icon-id');
        
        // Удаляем существующие обработчики чтобы избежать дублирования
        this.removeExistingHandlers(trigger);
        
        // Добавляем новый обработчик
        const clickHandler = (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Специальная обработка для QR-сканера
            if (iconId === 'qr-scanner' && window.qrScannerController) {
                this.openQrScanner(iconWrapper);
                return;
            }
            
            // Стандартное открытие модального окна
            this.openModal(modalId);
        };
        
        // Сохраняем обработчик для возможного удаления
        trigger._modalClickHandler = clickHandler;
        trigger.addEventListener('click', clickHandler);
        
        // Также добавляем обработчик на саму иконку-обертку
        if (iconWrapper !== trigger) {
            iconWrapper._modalClickHandler = clickHandler;
            iconWrapper.addEventListener('click', clickHandler);
        }
    }
    
    /**
     * Удаление существующих обработчиков
     */
    removeExistingHandlers(element) {
        if (element._modalClickHandler) {
            element.removeEventListener('click', element._modalClickHandler);
            delete element._modalClickHandler;
        }
    }    
    /**
     * Настройка базовых обработчиков событий
     */
    setupEventListeners() {
        // Обработчики закрытия модальных окон
        document.addEventListener('click', (e) => {
            const closeButton = e.target.closest('[data-modal-close]');
            if (closeButton) {
                e.preventDefault();
                this.closeModal();
            }
        });
        
        // Закрытие при клике на backdrop (если не запрещено)
        if (this.backdrop) {
            this.backdrop.addEventListener('click', (e) => {
                if (e.target === this.backdrop && this.activeModal && !this.activeModal.hasAttribute('data-static')) {
                    this.closeModal();
                }
            });
        }
        
        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.activeModal && !this.activeModal.hasAttribute('data-static')) {
                this.closeModal();
            }
        });
        
        // Глобальный обработчик для всех модальных триггеров
        document.addEventListener('click', (e) => {
            const modalTrigger = e.target.closest('[data-modal-target]');
            if (modalTrigger && !this.isActionDebounced()) {
                e.preventDefault();
                e.stopPropagation();
                
                const modalId = modalTrigger.getAttribute('data-modal-target');
                this.openModal(modalId);
            }
        });
    }
    
    /**
     * Настройка глобальных обработчиков
     */
    setupGlobalHandlers() {
        // Обработчики событий модальных окон
        document.addEventListener('modal.beforeOpen', (e) => this.handleBeforeOpen(e));
        document.addEventListener('modal.afterClose', (e) => this.handleAfterClose(e));
        
        // Интеграция с системой предотвращения скролла
        document.addEventListener('modal.opened', () => this.blockBodyScroll());
        document.addEventListener('modal.closed', () => this.unblockBodyScroll());
    }
    
    /**
     * Инициализация системы предотвращения конфликтов
     */
    initConflictPrevention() {
        // Глобальные флаги для совместимости
        window.modalClosingInProgress = false;
        window.modalOpeningInProgress = false;
        window.qrScannerBlockOpen = false;
        window.lastModalClosed = 0;
    }
    
    /**
     * Проверка на debounce действий
     */
    isActionDebounced() {
        const now = Date.now();
        if (now - this.lastActionTime < this.debounceDelay) {
            return true;
        }
        this.lastActionTime = now;
        return false;
    }
      
    /**
     * Открытие QR-сканера с интеграцией
     */
    openQrScanner(iconElement) {
        // Проверяем блокировки
        if (window.qrScannerBlockOpen || this.isOpening || this.isClosing) {
            return false;
        }
        
        // Используем специализированный контроллер если доступен
        if (window.qrScannerController && typeof window.qrScannerController.open === 'function') {
            return window.qrScannerController.open(iconElement);
        }
        
        // Запасной вариант - стандартное модальное окно
        return this.openModal('qrScannerModal');
    }
    
    /**
     * Универсальное открытие модального окна
     */
    openModal(modalId) {
        // Проверки на возможность открытия
        if (!this.canOpenModal(modalId)) {
            return false;
        }
        
        // Устанавливаем флаги состояния
        this.isOpening = true;
        window.modalOpeningInProgress = true;
        
        // Генерируем событие перед открытием
        const beforeOpenEvent = this.createEvent('modal.beforeOpen', { modalId });
        document.dispatchEvent(beforeOpenEvent);
        
        if (beforeOpenEvent.defaultPrevented) {
            this.resetOpeningState();
            return false;
        }
        
        // Закрываем текущее модальное окно если оно открыто
        if (this.activeModal) {
            this.closeModal(true);
        }
        
        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error(`ModalPanelSystem: Модальное окно ${modalId} не найдено`);
            this.resetOpeningState();
            return false;
        }
        
        // Выполняем открытие
        this.performModalOpen(modal, modalId);
        
        return true;
    }
    
    /**
     * Проверка возможности открытия модального окна
     */
    canOpenModal(modalId) {
        // Базовые проверки
        if (this.isOpening || this.isClosing || window.modalClosingInProgress) {
            return false;
        }
        
        // Проверка debounce
        if (this.isActionDebounced()) {
            return false;
        }
        
        // Специальная проверка для QR-сканера
        if (modalId === 'qrScannerModal' && window.qrScannerBlockOpen) {
            return false;
        }
        
        // Проверка времени последнего закрытия
        if (window.lastModalClosed && (Date.now() - window.lastModalClosed) < 1000) {
            return false;
        }
        
        return true;
    }
      /**
     * Выполнение открытия модального окна
     */
    performModalOpen(modal, modalId) {
        // Блокируем скролл body
        this.blockBodyScroll();
        
        // Активируем backdrop
        if (this.backdrop) {
            this.backdrop.classList.add('show');
        }
        
        // Показываем модальное окно с анимацией
        modal.classList.add('show', 'animate-in');
        modal.style.display = 'flex';
        
        // Устанавливаем активное модальное окно
        this.activeModal = modal;
        
        // Обеспечиваем доступность навигации
        setTimeout(() => {
            this.ensureNavigationAccessible();
        }, 100);
        
        // Инициализация специфичной логики модального окна
        this.initializeModalSpecifics(modalId);
        
        // Тактильная обратная связь
        this.provideFeedback();
        
        // Генерируем событие открытия
        this.dispatchModalEvent('modal.opened', modalId);
        
        // Сбрасываем состояние открытия
        setTimeout(() => this.resetOpeningState(), 300);
    }
    
    /**
     * Инициализация специфичной логики для разных типов модальных окон
     */
    initializeModalSpecifics(modalId) {
        switch (modalId) {
            case 'qrScannerModal':
                if (window.qrScannerController) {
                    // Инициализация через контроллер QR-сканера
                    setTimeout(() => {
                        if (typeof window.qrScannerController.initializeScanner === 'function') {
                            window.qrScannerController.initializeScanner();
                        }
                    }, 100);
                }
                break;
            case 'user-profile-modal':
                // Инициализация профиля пользователя
                if (typeof window.loadSupBalance === 'function') {
                    window.loadSupBalance();
                }
                break;
            case 'sub-profile-modal':
                // Инициализация формы пополнения баланса
                if (typeof window.calculateSup === 'function') {
                    window.calculateSup(100); // стандартная сумма
                }
                break;
        }
    }
    
    /**
     * Предоставление тактильной обратной связи
     */
    provideFeedback() {
        if (navigator.vibrate && 
            !window.matchMedia('(prefers-reduced-motion: reduce)').matches &&
            window.userHasInteractedWithPage) {
            try {
                navigator.vibrate(30);
            } catch (error) {
                // Игнорируем ошибки вибрации
            }
        }
    }
    
    /**
     * Сброс состояния открытия
     */
    resetOpeningState() {
        this.isOpening = false;
        window.modalOpeningInProgress = false;
    }
      
    /**
     * Универсальное закрытие модального окна
     */
    closeModal(immediate = false) {
        if (!this.activeModal || this.isClosing) {
            return false;
        }
        
        // Устанавливаем флаги закрытия
        this.isClosing = true;
        window.modalClosingInProgress = true;
        
        const modalId = this.activeModal.id;
        
        // Генерируем событие перед закрытием
        const beforeCloseEvent = this.createEvent('modal.beforeClose', { modalId });
        document.dispatchEvent(beforeCloseEvent);
        
        if (beforeCloseEvent.defaultPrevented) {
            this.resetClosingState();
            return false;
        }
        
        // Фиксируем время закрытия для предотвращения быстрого переоткрытия
        window.lastModalClosed = Date.now();
        
        // Выполняем закрытие
        this.performModalClose(modalId, immediate);
        
        return true;
    }
    
    /**
     * Выполнение закрытия модального окна
     */
    performModalClose(modalId, immediate = false) {
        // Специальная обработка для разных типов модальных окон
        this.handleModalSpecificClose(modalId);
        
        if (immediate || this.shouldCloseImmediately()) {
            this.closeImmediately(modalId);
        } else {
            this.closeWithAnimation(modalId);
        }
    }
    
    /**
     * Специальная обработка закрытия для разных типов модальных окон
     */
    handleModalSpecificClose(modalId) {
        switch (modalId) {
            case 'qrScannerModal':
                // Блокируем повторное открытие QR-сканера
                window.qrScannerBlockOpen = true;
                setTimeout(() => {
                    window.qrScannerBlockOpen = false;
                }, 2000);
                
                // Останавливаем сканер
                if (window.qrScannerController && typeof window.qrScannerController.stopScanner === 'function') {
                    try {
                        window.qrScannerController.stopScanner();
                    } catch (e) {
                        console.error('Ошибка при остановке QR-сканера:', e);
                    }
                }
                break;
            case 'sub-profile-modal':
                // Добавляем задержку для корректной обработки событий формы
                setTimeout(() => {
                    this.cleanupModalState(modalId);
                }, 100);
                return; // Выходим, чтобы не выполнять стандартное закрытие
        }
    }
    
    /**
     * Проверка на необходимость немедленного закрытия
     */
    shouldCloseImmediately() {
        return this.activeModal && this.activeModal.hasAttribute('data-static');
    }
    
    /**
     * Немедленное закрытие модального окна
     */
    closeImmediately(modalId) {
        // Скрываем backdrop
        if (this.backdrop) {
            this.backdrop.classList.remove('show');
        }
        
        // Скрываем модальное окно
        this.activeModal.classList.remove('show', 'animate-in');
        this.activeModal.style.display = 'none';
        
        // Очищаем состояние
        this.cleanupModalState(modalId);
    }
    
    /**
     * Закрытие с анимацией
     */
    closeWithAnimation(modalId) {
        // Запускаем анимацию закрытия
        if (this.backdrop) {
            this.backdrop.classList.remove('show');
        }
        
        this.activeModal.classList.remove('animate-in');
        this.activeModal.classList.add('animate-out');
        
        // Ждем завершения анимации
        setTimeout(() => {
            if (this.activeModal) {
                this.activeModal.classList.remove('show', 'animate-out');
                this.activeModal.style.display = 'none';
                
                this.cleanupModalState(modalId);
            }
        }, 300);
    }
    
    /**
     * Очистка состояния модального окна
     */
    cleanupModalState(modalId) {
        // Разблокируем скролл
        this.unblockBodyScroll();
        
        // Генерируем события закрытия
        this.dispatchModalEvent('modal.closed', modalId);
        this.dispatchModalEvent('modal.afterClose', modalId);
        
        // Очищаем активное модальное окно
        this.activeModal = null;
        
        // Сбрасываем флаги состояния
        setTimeout(() => this.resetClosingState(), 500);
    }
    
    /**
     * Сброс состояния закрытия
     */
    resetClosingState() {
        this.isClosing = false;
        window.modalClosingInProgress = false;
    }
    
    /**
     * Создание события с деталями
     */
    createEvent(type, detail) {
        return new CustomEvent(type, {
            detail: detail,
            bubbles: true,
            cancelable: true
        });
    }
    
    /**
     * Генерация события модального окна
     */
    dispatchModalEvent(eventType, modalId) {
        const sourceData = this.modalSources.get(modalId);
        const detail = {
            modalId: modalId,
            sourceIconId: sourceData?.iconId || null
        };
        
        const event = this.createEvent(eventType, detail);
        document.dispatchEvent(event);
        
        // Также генерируем событие на самом модальном окне
        if (this.activeModal) {
            this.activeModal.dispatchEvent(new Event(eventType.replace('modal.', '') + '.modal-panel'));
        }
    }
      
    /**
     * Обработчик события перед открытием
     */
    handleBeforeOpen(event) {
        const modalId = event.detail?.modalId;
        if (modalId && this.beforeOpenCallbacks.has(modalId)) {
            const callback = this.beforeOpenCallbacks.get(modalId);
            try {
                callback(event);
            } catch (error) {
                console.error(`Ошибка в callback перед открытием ${modalId}:`, error);
            }
        }
    }
    
    /**
     * Обработчик события после закрытия
     */
    handleAfterClose(event) {
        const modalId = event.detail?.modalId;
        if (modalId && this.afterCloseCallbacks.has(modalId)) {
            const callback = this.afterCloseCallbacks.get(modalId);
            try {
                callback(event);
            } catch (error) {
                console.error(`Ошибка в callback после закрытия ${modalId}:`, error);
            }
        }
    }
      /**
     * Интеграция с popup системой мобильной навигации
     */
    setupPopupIntegration() {
        if (!this.mobileNavIntegration || !this.mobileNavIntegration.popup) return;
        
        console.log('🔗 ModalPanelSystem: Настройка интеграции с popup системой');
        
        // Закрываем popup при открытии модального окна
        document.addEventListener('modal.beforeOpen', (event) => {
            const popup = this.mobileNavIntegration.popup;
            if (popup && popup.isPopupOpen) {
                console.log('🔽 ModalPanelSystem: Закрываем popup перед открытием модального окна');
                popup.closePopupForModal();
            }
        });
        
        // Дополнительная проверка при полном открытии модального окна
        document.addEventListener('modal.opened', (event) => {
            const popup = this.mobileNavIntegration.popup;
            if (popup && popup.isPopupOpen) {
                console.log('🔽 ModalPanelSystem: Принудительное закрытие popup при открытии модального окна');
                popup.closePopupForModal();
            }
        });
    }
      /**
     * Управление скроллом body с улучшенной логикой
     */
    blockBodyScroll() {
        if (this.scrollBlocked) return;
        
        if (window.mobileNavUtils && typeof window.mobileNavUtils.blockBodyScroll === 'function') {
            window.mobileNavUtils.blockBodyScroll();
        } else {
            // Резервная реализация
            const scrollY = window.pageYOffset || document.documentElement.scrollTop;
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${scrollY}px`;
            document.body.style.width = '100%';
            document.body.classList.add('modal-scroll-blocked');
            document.body.dataset.scrollY = scrollY;
        }
        
        // Обеспечиваем доступность мобильной навигации
        this.ensureNavigationAccessible();
        
        this.scrollBlocked = true;
    }
    
    unblockBodyScroll() {
        if (!this.scrollBlocked) return;
        
        if (window.mobileNavUtils && typeof window.mobileNavUtils.unblockBodyScroll === 'function') {
            window.mobileNavUtils.unblockBodyScroll();
        } else {
            // Резервная реализация
            const scrollY = parseInt(document.body.dataset.scrollY || '0', 10);
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            document.body.classList.remove('modal-scroll-blocked');
            window.scrollTo(0, scrollY);
        }
        
        // Обеспечиваем доступность мобильной навигации после разблокировки
        this.ensureNavigationAccessible();
        
        this.scrollBlocked = false;
    }
    
    /**
     * Обеспечивает доступность мобильной навигации
     */
    ensureNavigationAccessible() {
        // Ищем элементы мобильной навигации
        const navigation = document.querySelector('.mb-navigation');
        const scrollContainer = document.getElementById('nav-scroll-container');
        const iconsContainer = document.getElementById('nav-icons-container');
        
        if (navigation) {
            // Убираем классы блокировки
            navigation.classList.remove('popup-interaction-blocked');
            navigation.style.pointerEvents = '';
        }
        
        if (scrollContainer) {
            // Убираем блокировку с контейнера скролла
            scrollContainer.style.pointerEvents = '';
            scrollContainer.classList.remove('pointer-events-blocked');
        }
        
        if (iconsContainer) {
            // Убираем блокировку с контейнера иконок
            iconsContainer.style.pointerEvents = '';
            iconsContainer.classList.remove('pointer-events-blocked');
        }
        
        // Убираем блокировку с отдельных иконок
        const iconWrappers = document.querySelectorAll('.mb-icon-wrapper');
        iconWrappers.forEach(item => {
            item.style.pointerEvents = '';
            item.classList.remove('pointer-events-blocked');
        });
        
        console.log('ModalPanelSystem: Навигация разблокирована для взаимодействия');
    }
    
    /**
     * Блокировка глобального спиннера при открытии модальных окон
     */
    blockLoadingSpinner() {
        if (!window.loadingSpinner) return;
        
        // Скрываем спиннер, если он показан
        if (typeof window.loadingSpinner.forceHide === 'function') {
            window.loadingSpinner.forceHide();
        }
        
        // Временно блокируем метод show
        const originalShow = window.loadingSpinner.show;
        window.loadingSpinner.show = function() { 
            console.log('LoadingSpinner.show заблокирован модальной системой');
        };
        
        // Восстанавливаем метод через короткую задержку
        setTimeout(() => {
            window.loadingSpinner.show = originalShow;
        }, 800);
        
        // Добавляем маркер состояния
        document.body.classList.add('modal-active');
        setTimeout(() => {
            document.body.classList.remove('modal-active');
        }, 800);
    }
    
    /**
     * Регистрация callback'а перед открытием модального окна
     */
    onBeforeOpen(modalId, callback) {
        this.beforeOpenCallbacks.set(modalId, callback);
    }
    
    /**
     * Регистрация callback'а после закрытия модального окна
     */
    onAfterClose(modalId, callback) {
        this.afterCloseCallbacks.set(modalId, callback);
    }
    
    /**
     * Получение информации о текущем активном модальном окне
     */
    getActiveModal() {
        return this.activeModal ? {
            id: this.activeModal.id,
            element: this.activeModal,
            source: this.modalSources.get(this.activeModal.id)
        } : null;
    }
    
    /**
     * Проверка, открыто ли модальное окно
     */
    isModalOpen(modalId = null) {
        if (modalId) {
            return this.activeModal && this.activeModal.id === modalId;
        }
        return this.activeModal !== null;
    }
    
    /**
     * Деструктор для очистки ресурсов
     */
    destroy() {
        // Закрываем активное модальное окно
        if (this.activeModal) {
            this.closeModal(true);
        }
        
        // Очищаем все слушатели и callbacks
        this.modalSources.clear();
        this.beforeOpenCallbacks.clear();
        this.afterCloseCallbacks.clear();
        
        // Удаляем ссылки на элементы
        this.backdrop = null;
        this.mobileNavIntegration = null;
        
        console.log('ModalPanelSystem: Система очищена');
    }
}

// Создаем глобальный экземпляр унифицированной системы модальных окон
window.modalPanel = new ModalPanelSystem();

// ===== ГЛОБАЛЬНЫЕ API ФУНКЦИИ =====

/**
 * Унифицированная функция для открытия модального окна
 */
window.openModalPanel = function(modalId, options = {}) {
    if (!window.modalPanel) {
        console.error('ModalPanelSystem не инициализирована');
        return false;
    }
    
    // Поддержка опций
    if (options.beforeOpen && typeof options.beforeOpen === 'function') {
        window.modalPanel.onBeforeOpen(modalId, options.beforeOpen);
    }
    
    if (options.afterClose && typeof options.afterClose === 'function') {
        window.modalPanel.onAfterClose(modalId, options.afterClose);
    }
    
    return window.modalPanel.openModal(modalId);
};

/**
 * Унифицированная функция для закрытия модального окна
 */
window.closeModalPanel = function(immediate = false) {
    if (!window.modalPanel) {
        console.error('ModalPanelSystem не инициализирована');
        return false;
    }
    
    return window.modalPanel.closeModal(immediate);
};

/**
 * Специализированная функция для QR-сканера
 */
window.openQrScannerModal = function(iconElement) {
    if (!window.modalPanel) {
        console.error('ModalPanelSystem не инициализирована');
        return false;
    }
    
    return window.modalPanel.openQrScanner(iconElement);
};

/**
 * Проверка состояния модальных окон
 */
window.isModalOpen = function(modalId = null) {
    if (!window.modalPanel) {
        return false;
    }
    
    return window.modalPanel.isModalOpen(modalId);
};

/**
 * Получение информации об активном модальном окне
 */
window.getActiveModal = function() {
    if (!window.modalPanel) {
        return null;
    }
    
    return window.modalPanel.getActiveModal();
};

/**
 * Регистрация обработчиков для модальных окон
 */
window.onModalBeforeOpen = function(modalId, callback) {
    if (window.modalPanel && typeof callback === 'function') {
        window.modalPanel.onBeforeOpen(modalId, callback);
    }
};

window.onModalAfterClose = function(modalId, callback) {
    if (window.modalPanel && typeof callback === 'function') {
        window.modalPanel.onAfterClose(modalId, callback);
    }
};

// ===== СОВМЕСТИМОСТЬ И LEGACY SUPPORT =====

// Поддержка старых функций для обратной совместимости
if (!window.openModal) {
    window.openModal = window.openModalPanel;
}

if (!window.closeModal) {
    window.closeModal = window.closeModalPanel;
}

// Автоматическая очистка при выгрузке страницы
window.addEventListener('beforeunload', () => {
    if (window.modalPanel && typeof window.modalPanel.destroy === 'function') {
        window.modalPanel.destroy();
    }
});

// Логирование для отладки
console.log('🎭 ModalPanelSystem: Унифицированная система модальных окон инициализирована');

// Экспорт для модульных систем (если требуется)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { ModalPanelSystem };
}

if (typeof window.define === 'function' && window.define.amd) {
    window.define('ModalPanelSystem', [], function() {
        return ModalPanelSystem;
    });
}
</script>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/layouts/partials/modal/modal-system.blade.php ENDPATH**/ ?>