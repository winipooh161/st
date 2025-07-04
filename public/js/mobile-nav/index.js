/**
 * Основной модуль мобильной навигации
 * Интегрирует все компоненты мобильной навигации и предоставляет публичный API
 */

// Режим совместимости для старых браузеров
(function(window) {
    console.log('✅ MobileNav Index: Модуль навигации загружен');
    
    // Основной класс мобильной навигации
    class MobileNav {
        constructor() {
            this.initialized = false;
            this.scrollPosition = 0;
            this.navItems = [];
            this.activeSection = null;
            
            // Настройки по умолчанию
            this.settings = {
                animationDuration: 300,
                scrollThreshold: 100,
                useNativeScroll: true,
                debug: false
            };
            
            // Инициализируем при загрузке DOM
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.init());
            } else {
                this.init();
            }
        }
        
        // Инициализация компонента
        init() {
            if (this.initialized) return;
            
            console.log('🔄 MobileNav: Инициализация...');
            
            // Находим DOM элементы
            this.container = document.querySelector('.mobile-nav-container');
            if (!this.container) {
                console.warn('⚠️ MobileNav: .mobile-nav-container не найден в DOM');
                return;
            }
            
            this.navBar = this.container.querySelector('.mobile-nav-bar');
            this.content = this.container.querySelector('.mobile-nav-content');
            
            if (!this.navBar || !this.content) {
                console.warn('⚠️ MobileNav: Необходимые элементы навигации не найдены');
                return;
            }
            
            // Находим и сохраняем элементы навигации
            this.navItems = Array.from(this.navBar.querySelectorAll('.nav-item'));
            this.navItems.forEach(item => {
                item.addEventListener('click', (e) => this.handleNavClick(e));
            });
            
            // Настройка прокрутки
            this.setupScrolling();
            
            // Инициализация завершена
            this.initialized = true;
            console.log('✅ MobileNav: Инициализация завершена');
            
            // Устанавливаем активную секцию по умолчанию
            this.activateSection(0);
            
            // Публикуем событие инициализации
            window.dispatchEvent(new CustomEvent('mobilenav:initialized'));
        }
        
        // Настройка прокрутки
        setupScrolling() {
            if (this.settings.useNativeScroll) {
                // Используем нативную прокрутку
                this.content.addEventListener('scroll', () => this.handleScroll());
            } else {
                // Используем кастомную прокрутку с помощью touch событий
                let startY = 0;
                let currentY = 0;
                
                this.content.addEventListener('touchstart', (e) => {
                    startY = e.touches[0].clientY;
                    currentY = this.scrollPosition;
                });
                
                this.content.addEventListener('touchmove', (e) => {
                    const deltaY = startY - e.touches[0].clientY;
                    this.scrollTo(currentY + deltaY);
                    e.preventDefault();
                });
            }
        }
        
        // Обработчик прокрутки
        handleScroll() {
            this.scrollPosition = this.content.scrollTop;
            
            // Обновляем активную секцию на основе позиции прокрутки
            const sections = Array.from(this.content.querySelectorAll('.mobile-nav-section'));
            
            let activeIndex = 0;
            const viewportHeight = window.innerHeight;
            const scrollThreshold = viewportHeight * 0.3;
            
            sections.forEach((section, index) => {
                const rect = section.getBoundingClientRect();
                if (rect.top <= scrollThreshold && rect.bottom >= scrollThreshold) {
                    activeIndex = index;
                }
            });
            
            // Обновляем состояние навигации
            this.activateSection(activeIndex, false);
            
            // Публикуем событие прокрутки
            window.dispatchEvent(new CustomEvent('mobilenav:scroll', {
                detail: { position: this.scrollPosition }
            }));
        }
        
        // Обработчик клика по элементу навигации
        handleNavClick(e) {
            e.preventDefault();
            
            const navItem = e.currentTarget;
            const index = this.navItems.indexOf(navItem);
            
            if (index !== -1) {
                this.activateSection(index, true);
            }
        }
        
        // Активация секции по индексу
        activateSection(index, scroll = true) {
            // Находим секцию по индексу
            const sections = Array.from(this.content.querySelectorAll('.mobile-nav-section'));
            if (index < 0 || index >= sections.length) return;
            
            const section = sections[index];
            
            // Обновляем активный элемент навигации
            this.navItems.forEach(item => item.classList.remove('active'));
            if (this.navItems[index]) {
                this.navItems[index].classList.add('active');
            }
            
            // Сохраняем активную секцию
            this.activeSection = section;
            
            // Прокручиваем к активной секции при необходимости
            if (scroll) {
                this.scrollToSection(section);
            }
            
            // Публикуем событие изменения секции
            window.dispatchEvent(new CustomEvent('mobilenav:section-change', {
                detail: { index, section }
            }));
        }
        
        // Прокрутка к указанной секции
        scrollToSection(section) {
            if (this.settings.useNativeScroll) {
                section.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            } else {
                const sectionTop = section.offsetTop;
                this.scrollTo(sectionTop, true);
            }
        }
        
        // Прокрутка к указанной позиции
        scrollTo(position, animate = false) {
            if (this.settings.useNativeScroll) {
                this.content.scrollTo({
                    top: position,
                    behavior: animate ? 'smooth' : 'auto'
                });
            } else {
                if (animate) {
                    // Анимация прокрутки
                    const start = this.scrollPosition;
                    const change = position - start;
                    const startTime = performance.now();
                    
                    const animate = (currentTime) => {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / this.settings.animationDuration, 1);
                        
                        // Функция плавности
                        const easeOut = (t) => 1 - Math.pow(1 - t, 2);
                        const currentPosition = start + change * easeOut(progress);
                        
                        this.updateScrollPosition(currentPosition);
                        
                        if (progress < 1) {
                            requestAnimationFrame(animate);
                        }
                    };
                    
                    requestAnimationFrame(animate);
                } else {
                    this.updateScrollPosition(position);
                }
            }
        }
        
        // Обновление позиции прокрутки
        updateScrollPosition(position) {
            this.scrollPosition = position;
            this.content.style.transform = `translateY(-${position}px)`;
        }
        
        // Публичный метод для обновления настроек
        updateSettings(newSettings) {
            Object.assign(this.settings, newSettings);
            console.log('🔄 MobileNav: Настройки обновлены', this.settings);
        }
        
        // Публичный метод для получения текущего состояния
        getState() {
            return {
                initialized: this.initialized,
                scrollPosition: this.scrollPosition,
                activeSection: this.activeSection ? Array.from(this.content.querySelectorAll('.mobile-nav-section')).indexOf(this.activeSection) : null,
                settings: { ...this.settings }
            };
        }
    }
    
    // Создаём и экспортируем экземпляр
    const mobileNavInstance = new MobileNav();
    
    // Регистрируем глобально для доступа из других модулей
    window.MobileNav = mobileNavInstance;
    
})(window);
