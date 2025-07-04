import { MobileNavCore } from './MobileNavCore.js';
import { MobileNavEvents } from './MobileNavEvents.js';
import { MobileNavScroll } from './MobileNavScroll.js';
import { MobileNavPopup } from './MobileNavPopup.js';
import { MobileNavStorage } from './MobileNavStorage.js';
import { MobileNavUtils } from './MobileNavUtils.js';

class MobileNavWheelPicker {
    constructor() {
        console.log('🎡 MobileNavWheelPicker: Начало инициализации');
        
        // Создаем компоненты с обработкой ошибок инициализации
        try {
            this.core = new MobileNavCore();
            console.log('✅ MobileNavCore инициализировано');
            
            this.storage = new MobileNavStorage();
            console.log('✅ MobileNavStorage инициализировано');
            
            this.scroll = new MobileNavScroll(this.core);
            console.log('✅ MobileNavScroll инициализировано');
            
            this.popup = new MobileNavPopup(this.core);
            console.log('✅ MobileNavPopup инициализировано');
            
            this.utils = new MobileNavUtils(this.core, this.scroll);
            console.log('✅ MobileNavUtils инициализировано');
            
            this.events = new MobileNavEvents(this.core, this.scroll, this.popup);
            console.log('✅ MobileNavEvents инициализировано');
        } catch (error) {
            console.error('⛔ Ошибка при инициализации компонентов навигации:', error);
        }
        
        // Регистрируем в глобальной области для доступа из других скриптов
        window.MobileNavWheelPicker = this;
        
        // Инициализируем систему отслеживания скролла страницы с задержкой
        setTimeout(() => {
            try {
                this.initScrollTracking();
                console.log('✅ Отслеживание скролла инициализировано');
            } catch (error) {
                console.warn('⚠️ Ошибка инициализации отслеживания скролла:', error);
            }
        }, 300);
        
        // Инициализируем интеграцию с модальными окнами с задержкой
        setTimeout(() => {
            try {
                this.initModalIntegration();
                console.log('✅ Интеграция с модальной системой инициализирована');
            } catch (error) {
                console.warn('⚠️ Ошибка интеграции с модальной системой:', error);
            }
        }, 500);
        
        // Настраиваем API для внешнего использования
        this.setupPublicAPI();
        
        console.log('🎡 MobileNavWheelPicker: Инициализация завершена');
    }

    initModalIntegration() {
        // Ждем инициализации модальной системы
        if (window.modalPanel) {
            this.connectToModalSystem();
        } else {
            // Проверяем периодически до инициализации
            const checkInterval = setInterval(() => {
                if (window.modalPanel) {
                    this.connectToModalSystem();
                    clearInterval(checkInterval);
                }
            }, 100);
            
            // Прекращаем попытки через 5 секунд
            setTimeout(() => {
                clearInterval(checkInterval);
                console.warn('MobileNavWheelPicker: Модальная система не найдена, работаем без интеграции');
            }, 5000);
        }
    }
    
    connectToModalSystem() {
        console.log('🔗 MobileNavWheelPicker: Подключение к модальной системе');
        
        // Информируем модальную систему о нашем присутствии
        if (window.modalPanel.mobileNavIntegration !== this) {
            window.modalPanel.mobileNavIntegration = this;
            console.log('✅ MobileNavWheelPicker: Интеграция с модальной системой установлена');
        }
        
        // Регистрируем обработчики для улучшенной интеграции
        this.registerModalHandlers();
    }
    
    registerModalHandlers() {
        // Обработчик перед открытием модального окна
        if (window.onModalBeforeOpen) {
            window.onModalBeforeOpen('qrScannerModal', (event) => {
                console.log('MobileNavWheelPicker: Подготовка к открытию QR-сканера');
                // Закрываем popup если он открыт
                if (this.popup && this.popup.isPopupOpen) {
                    this.popup.closePopup();
                }
            });
            
            // Универсальный обработчик для всех модальных окон
            document.addEventListener('modal.beforeOpen', (event) => {
                // Блокируем горизонтальный скролл при открытии модальных окон
                if (this.scroll && typeof this.scroll.blockHorizontalScroll === 'function') {
                    this.scroll.blockHorizontalScroll();
                }
            });
        }
        
        // Обработчик после закрытия модального окна  
        if (window.onModalAfterClose) {
            document.addEventListener('modal.afterClose', (event) => {
                // Разблокируем горизонтальный скролл после закрытия
                if (this.scroll && typeof this.scroll.unblockHorizontalScroll === 'function') {
                    this.scroll.unblockHorizontalScroll();
                }
            });
        }
    }
    
    setupPublicAPI() {
        // Публичный API для внешнего использования
        this.api = {
            // Управление навигацией
            show: () => this.core.showNavigation(),
            hide: () => this.core.hideNavigation(),
            
            // Работа с иконками
            convertToBackButton: (iconId) => this.core.convertIconToBackButton(iconId),
            restoreIcon: (iconId) => this.core.restoreIcon(iconId),
            
            // Popup управление
            showPopup: (iconId) => this.popup.showPopup(iconId),
            closePopup: () => this.popup.closePopup(),
            
            // Состояние
            isPopupOpen: () => this.popup.isPopupOpen,
            getActiveIconId: () => this.events.activeIconId,
            
            // Интеграция
            modalSystemConnected: () => window.modalPanel && window.modalPanel.mobileNavIntegration === this
        };
          // Регистрируем API в глобальной области
        window.mobileNavAPI = this.api;
    }
    
    // Метод для принудительной переинициализации
    reinitialize() {
        console.log('🔄 MobileNavWheelPicker: Переинициализация');
        
        // Переинициализируем ядро
        if (this.core) {
            this.core.init();
        }
        
        // Переустанавливаем обработчики событий
        if (this.events) {
            this.events.init();
        }
        
        // Повторно подключаемся к модальной системе
        this.initModalIntegration();
    }
    
    // Метод для очистки ресурсов
    destroy() {
        console.log('🗑️ MobileNavWheelPicker: Очистка ресурсов');
        
        // Закрываем popup если открыт
        if (this.popup && this.popup.isPopupOpen) {
            this.popup.closePopup();
        }
        
        // Восстанавливаем все иконки
        if (this.events && this.events.activeIconId) {
            this.core.restoreIcon(this.events.activeIconId);
        }
          // Удаляем ссылки
        delete window.MobileNavWheelPicker;
        delete window.mobileNavAPI;
        
        // Информируем модальную систему
        if (window.modalPanel && window.modalPanel.mobileNavIntegration === this) {
            window.modalPanel.mobileNavIntegration = null;
        }
    }
    
    // Метод для инициализации отслеживания скролла страницы
    initScrollTracking() {
        // Ждем полной загрузки DOM
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => this.setupScrollListeners(), 300);
            });
        } else {
            setTimeout(() => this.setupScrollListeners(), 300);
        }
    }
    
    // Метод для настройки слушателей скролла
    setupScrollListeners() {
        if (this.scroll) {
            // Настраиваем отслеживание скролла страницы для скрытия/показа навигации
            this.scroll.setupPageScrollListener();
            
            // Настраиваем обработчики активности пользователя
            this.scroll.setupUserActivityListeners();
            
            console.log('📜 MobileNavWheelPicker: Отслеживание скролла инициализировано');
        }
    }
}

// Создаем экземпляр после загрузки DOM
document.addEventListener('DOMContentLoaded', () => {
    // Инициализируем с небольшой задержкой для гарантии загрузки всех элементов
    setTimeout(() => new MobileNavWheelPicker(), 100);
});

export default MobileNavWheelPicker;
