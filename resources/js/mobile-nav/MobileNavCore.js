export class MobileNavCore {
    constructor() {
        // Основные элементы
        this.container = null;
        this.iconsContainer = null;
        this.items = [];
        this.centerPoint = 0;
        this.sidePadding = 16; // Стандартный отступ по бокам
        
        // Состояние
        this.isInitialized = false;
        this.activeIconId = null;
        this.originalIcons = new Map(); // Для хранения оригинальных иконок        // Инициализация после создания объекта
        this.init();
        
        // Инициализация с проверкой страницы редактора
        this.checkEditorPage();
        
        // Слушаем изменения URL
        window.addEventListener('popstate', () => this.checkEditorPage());
    }
      init() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                this.findElements();
                this.initModalEventHandlers();
            });
        } else {
            // DOM уже загружен
            setTimeout(() => {
                this.findElements();
                this.initModalEventHandlers();
            }, 100);
        }
    }
    
    findElements() {
        // Находим контейнер для скролла
        this.container = document.getElementById('nav-scroll-container');
        
        // Находим контейнер с иконками
        this.iconsContainer = document.getElementById('nav-icons-container');
        
        // Проверяем, найдены ли все элементы
        if (!this.container || !this.iconsContainer) {
            console.warn('MobileNavCore: Не все элементы найдены, повторная попытка через 500ms');
            setTimeout(() => this.findElements(), 500);
            return;
        }
        
        // Находим все иконки
        this.items = Array.from(this.iconsContainer.querySelectorAll('.mb-icon-wrapper'));
        
        // Вычисляем центральную точку контейнера
        this.calculateCenterPoint();
        
        // Если есть иконки, инициализируем навигацию
        if (this.items.length > 0) {
            this.isInitialized = true;
            this.setupIconDistribution(); // Настраиваем распределение иконок
            console.log('MobileNavCore: Навигация инициализирована');
        }
        
        // Настраиваем распределение иконок
        this.setupIconDistribution();
    }
    
    calculateCenterPoint() {
        // Центральная точка - середина контейнера
        this.centerPoint = this.container ? this.container.offsetWidth / 2 : 0;
    }
    
    // Скрытие навигационной панели
    hideNavigation() {
        const navigation = document.querySelector('.mb-navigation');
        if (navigation) {
            navigation.classList.add('mb-nav-hidden');
        }
    }
    
    // Показ навигационной панели
    showNavigation() {
        const navigation = document.querySelector('.mb-navigation');
        if (navigation) {
            navigation.classList.remove('mb-nav-hidden');
        }
    }
    
    // Преобразование иконки в кнопку "назад"
    convertIconToBackButton(iconId) {
        if (!this.isInitialized) {
            console.warn('MobileNavCore: Невозможно преобразовать иконку в кнопку "назад" - ядро не инициализировано');
            return false;
        }
        
        // Принудительно показываем навигационную панель
        this.showNavigation();
        
        // Запоминаем эту иконку как активную
        this.activeIconId = iconId;
        
        // Если у нас уже активна другая иконка, восстанавливаем её
        if (this.activeIconId && this.activeIconId !== iconId) {
            this.restoreIcon(this.activeIconId);
        }
        
        // Находим иконку по ID с повышенной надёжностью
        let iconElement = null;
        
        // Пробуем найти через data-icon-id
        iconElement = this.items.find(item => item.getAttribute('data-icon-id') === iconId);
        
        // Если не нашли, пробуем через id
        if (!iconElement) {
            iconElement = document.getElementById(`nav-icon-${iconId}`);
        }
        
        // Если всё еще не нашли, ищем по другим атрибутам
        if (!iconElement) {
            iconElement = document.querySelector(`[data-nav-id="${iconId}"], .mb-icon-wrapper[data-id="${iconId}"]`);
        }
        
        if (!iconElement) {
            console.warn(`⚠️ MobileNavCore: Иконка с ID ${iconId} не найдена, невозможно преобразовать в кнопку "назад"`);
            return false;
        }
        
        // Сохраняем оригинальное состояние иконки
        const iconLink = iconElement.querySelector('a');
        const iconImg = iconElement.querySelector('.mb-nav-icon');
        
        if (iconLink && iconImg) {
            console.log(`✅ Преобразование иконки ${iconId} в кнопку "назад"`);
            
            // Сохраняем оригинальное содержимое, если еще не сохранено
            if (!this.originalIcons.has(iconId)) {
                this.originalIcons.set(iconId, {
                    link: iconLink.getAttribute('href'),
                    img: iconImg.getAttribute('src'),
                    text: iconElement.querySelector('.mb-icon-title')?.textContent || '',
                    classes: iconElement.className,
                    linkClasses: iconLink.className,
                    onclick: iconLink.onclick
                });
                console.log('💾 Сохранено оригинальное состояние иконки', iconId);
            }
            
            // Меняем на кнопку "назад"
            iconLink.setAttribute('href', 'javascript:void(0);');
            iconLink.classList.add('mb-nav-back-btn');
            
            // Если есть текст иконки, меняем его на "Назад"
            const iconTitle = iconElement.querySelector('.mb-icon-title');
            if (iconTitle) {
                iconTitle.textContent = 'Назад';
            }
            
            // Меняем иконку на стрелку назад (с проверкой на существование)
            const arrowIconPath = '/images/icons/arrow-left.svg';
            
            // Проверяем существование файла (через создание временного Image)
            const tmpImg = new Image();
            tmpImg.onload = () => {
                // Если файл существует, устанавливаем его
                iconImg.setAttribute('src', arrowIconPath);
                iconElement.classList.add('back-button-active');
                console.log('🔙 Установлена иконка "назад"');
            };
            tmpImg.onerror = () => {
                // Если файл не существует, используем Unicode-символ
                iconImg.style.backgroundImage = 'none';
                iconImg.textContent = '←';
                iconImg.style.fontSize = '24px';
                iconImg.style.textAlign = 'center';
                iconImg.style.lineHeight = '24px';
                iconElement.classList.add('back-button-active');
                console.log('🔙 Установлен символ "←" вместо иконки');
            };
            tmpImg.src = arrowIconPath;
            
            // Удаляем предыдущий обработчик события (если есть)
            if (iconLink._closeModalHandler) {
                iconLink.removeEventListener('click', iconLink._closeModalHandler);
                iconLink.onclick = null; // Очищаем прямой обработчик
            }
            
            // Создаем обработчик для закрытия модального окна
            iconLink._closeModalHandler = (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                console.log('👆 Нажата кнопка "Назад" для закрытия модального окна');
                
                // Пробуем разными способами закрыть модальное окно
                if (window.modalPanel && typeof window.modalPanel.closeModal === 'function') {
                    console.log('✅ Закрываем через window.modalPanel.closeModal()');
                    window.modalPanel.closeModal();
                } else if (window.closeModalPanel) {
                    console.log('✅ Закрываем через window.closeModalPanel()');
                    window.closeModalPanel();
                } else {
                    console.log('⚠️ Не найден метод для закрытия модального окна');
                    
                    // Ищем открытое модальное окно напрямую
                    const openModal = document.querySelector('.modal-panel.show, .modal-panel.fade.show');
                    if (openModal) {
                        console.log('✅ Закрываем модальное окно напрямую через classList');
                        openModal.classList.remove('show');
                        setTimeout(() => {
                            if (openModal.parentNode) {
                                openModal.classList.add('d-none');
                            }
                        }, 300);
                    }
                    
                    // В качестве последнего средства генерируем событие
                    document.dispatchEvent(new CustomEvent('modal.close.requested', {
                        bubbles: true,
                        cancelable: true
                    }));
                }
                
                // Восстанавливаем оригинальную иконку через небольшую задержку
                setTimeout(() => {
                    this.restoreIcon(iconId);
                }, 100);
                
                return false;
            };
            
            // Добавляем обработчик для закрытия модального окна
            iconLink.addEventListener('click', iconLink._closeModalHandler);
            
            // Добавляем прямой обработчик onclick как запасной вариант
            iconLink.onclick = iconLink._closeModalHandler;
            
            // Добавляем обработчик на touchend для улучшенной мобильной поддержки
            iconLink.addEventListener('touchend', iconLink._closeModalHandler);
            
            // Устанавливаем активную иконку
            this.activeIconId = iconId;
            
            return true;
        }
        
        return false;
    }
    
    // Восстановление оригинальной иконки
    restoreIcon(iconId) {
        if (!this.isInitialized) {
            console.warn('MobileNavCore: Невозможно восстановить иконку - ядро не инициализировано');
            return false;
        }
        
        // Проверяем, сохранены ли оригинальные данные для этой иконки
        if (!this.originalIcons.has(iconId)) {
            console.log(`⚠️ Нет сохраненных данных для восстановления иконки ${iconId}`);
            return false;
        }
        
        console.log(`🔄 Восстанавливаем оригинальную иконку ${iconId}`);
        
        // Находим иконку по ID с повышенной надёжностью
        let iconElement = null;
        
        // Пробуем найти через data-icon-id
        iconElement = this.items.find(item => item.getAttribute('data-icon-id') === iconId);
        
        // Если не нашли, пробуем через id
        if (!iconElement) {
            iconElement = document.getElementById(`nav-icon-${iconId}`);
        }
        
        // Если всё еще не нашли, ищем по другим атрибутам
        if (!iconElement) {
            iconElement = document.querySelector(`[data-nav-id="${iconId}"], .mb-icon-wrapper[data-id="${iconId}"]`);
        }
        
        if (!iconElement) {
            console.warn(`⚠️ MobileNavCore: Иконка с ID ${iconId} для восстановления не найдена`);
            return false;
        }
        
        // Получаем оригинальные данные
        const originalData = this.originalIcons.get(iconId);
        
        // Находим элементы для восстановления
        const iconLink = iconElement.querySelector('a');
        const iconImg = iconElement.querySelector('.mb-nav-icon');
        
        if (iconLink && iconImg && originalData) {
            // Удаляем обработчик закрытия модального окна
            if (iconLink._closeModalHandler) {
                iconLink.removeEventListener('click', iconLink._closeModalHandler);
                iconLink.removeEventListener('touchend', iconLink._closeModalHandler);
                delete iconLink._closeModalHandler;
            }
            
            // Восстанавливаем оригинальное состояние
            iconLink.setAttribute('href', originalData.link);
            iconLink.className = originalData.linkClasses || '';
            
            // Восстанавливаем оригинальную иконку
            iconImg.setAttribute('src', originalData.img);
            
            // Восстанавливаем оригинальный текст иконки, если был сохранен
            if (originalData.text !== undefined) {
                const iconTitle = iconElement.querySelector('.mb-icon-title');
                if (iconTitle) {
                    iconTitle.textContent = originalData.text;
                }
            }
            
            // Восстанавливаем оригинальный обработчик события onclick
            if (originalData.onclick) {
                iconLink.onclick = originalData.onclick;
            }
            
            // Восстанавливаем оригинальные стили, если они были изменены
            iconImg.style.backgroundImage = '';
            iconImg.style.fontSize = '';
            iconImg.style.textAlign = '';
            iconImg.style.lineHeight = '';
            iconImg.textContent = '';
            
            // Восстанавливаем классы элемента
            iconElement.className = originalData.classes;
            
            // Удаляем класс активной кнопки "назад"
            iconElement.classList.remove('back-button-active');
            
            // Удаляем прямой обработчик onclick
            iconLink.onclick = null;
            
            console.log(`✅ Иконка ${iconId} успешно восстановлена`);
            
            // Удаляем из сохраненных оригиналов
            this.originalIcons.delete(iconId);
            
            // Если это была активная иконка, сбрасываем активную иконку
            if (this.activeIconId === iconId) {
                this.activeIconId = null;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Проверяет, находимся ли на странице редактора и обеспечивает видимость навигации
     */
    checkEditorPage() {
        const currentPath = window.location.pathname;
        const isEditorPage = currentPath.includes('/templates/editor') || 
                           currentPath.includes('/client/templates/editor');
        
        if (isEditorPage) {
            this.ensureNavigationVisible();
        }
    }
    
    /**
     * Обеспечивает видимость навигационной панели
     */
    ensureNavigationVisible() {
        const navigation = document.querySelector('.mb-navigation');
        if (navigation) {
            navigation.classList.remove('mb-nav-hidden');
            // Убираем все возможные стили блокировки
            this.ensureNavigationAccessible();
        }
    }
    
    /**
     * Обеспечивает доступность навигации (снимает блокировки pointer-events)
     */
    ensureNavigationAccessible() {
        const navigation = document.querySelector('.mb-navigation');
        const container = this.container;
        const iconsContainer = this.iconsContainer;
        
        if (navigation) {
            // Убираем классы блокировки
            navigation.classList.remove('popup-interaction-blocked');
            navigation.style.pointerEvents = '';
        }
        
        if (container) {
            // Убираем блокировку с контейнера скролла
            container.style.pointerEvents = '';
            container.classList.remove('pointer-events-blocked');
        }
        
        if (iconsContainer) {
            // Убираем блокировку с контейнера иконок
            iconsContainer.style.pointerEvents = '';
            iconsContainer.classList.remove('pointer-events-blocked');
        }
        
        // Убираем блокировку с отдельных иконок
        this.items.forEach(item => {
            item.style.pointerEvents = '';
            item.classList.remove('pointer-events-blocked');
        });
        
        console.log('MobileNavCore: Навигация разблокирована для взаимодействия');
    }
    
    /**
     * Блокирует взаимодействие с навигацией (если необходимо)
     */
    blockNavigationInteraction() {
        const navigation = document.querySelector('.mb-navigation');
        
        if (navigation) {
            navigation.classList.add('popup-interaction-blocked');
        }
        
        console.log('MobileNavCore: Взаимодействие с навигацией заблокировано');
    }
    
    /**
     * Инициализация обработчиков модальных событий
     */
    initModalEventHandlers() {
        // Слушаем события модальных окон
        document.addEventListener('modal.opened', (event) => {
            console.log('MobileNavCore: Модальное окно открыто, обеспечиваем доступность навигации');
            // При открытии модального окна обеспечиваем доступность навигации
            setTimeout(() => {
                this.ensureNavigationAccessible();
            }, 100);
        });
        
        document.addEventListener('modal.closed', (event) => {
            console.log('MobileNavCore: Модальное окно закрыто, восстанавливаем навигацию');
            // При закрытии модального окна также обеспечиваем доступность
            setTimeout(() => {
                this.ensureNavigationAccessible();
            }, 100);
        });
        
        // Также слушаем события от унифицированной модальной системы
        document.addEventListener('modal.beforeOpen', (event) => {
            this.ensureNavigationAccessible();
        });
        
        document.addEventListener('modal.afterClose', (event) => {
            this.ensureNavigationAccessible();
        });
    }
    
    // Метод для правильного распределения иконок в контейнере
    setupIconDistribution() {
        if (!this.iconsContainer || !this.items.length) return;
        
        const iconCount = this.items.length;
        
        // Добавляем data-атрибут с количеством иконок
        this.iconsContainer.setAttribute('data-icon-count', iconCount.toString());
        
        // Добавляем CSS класс для правильного распределения
        this.iconsContainer.classList.remove('icons-2', 'icons-3', 'icons-4', 'icons-5');
        
        if (iconCount <= 5) {
            this.iconsContainer.classList.add(`icons-${iconCount}`);
        }
        
        // Для каждой иконки устанавливаем правильную ширину
        this.items.forEach((item, index) => {
            const percentage = 100 / iconCount;
            item.style.flex = `0 0 ${percentage}%`;
            item.style.maxWidth = `${percentage}%`;
        });
        
        console.log(`MobileNavCore: Настроено распределение для ${iconCount} иконок`);
    }
}
