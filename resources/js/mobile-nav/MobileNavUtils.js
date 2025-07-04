export class MobileNavUtils {
    constructor(core, scroll) {
        this.core = core;
        this.scroll = scroll;
    }

    // Методы центрирования удалены, так как используется фиксированная раскладка 4 иконок

    // Метод для получения информации о навигации (упрощенный)
    getNavigationInfo() {
        const containerWidth = this.core.container ? this.core.container.offsetWidth : 0;
        const iconsCount = this.core.items ? this.core.items.length : 0;
        
        return {
            containerWidth,
            iconsCount,
            isFixedLayout: true, // Всегда фиксированная раскладка
            maxIcons: 4 // Максимум 4 иконки в видимой области
        };
    }

    // Метод для проверки видимости иконок (упрощенный)
    setupVisibilityObserver() {
        if (!('IntersectionObserver' in window)) return;

        const observerOptions = {
            root: null, // Используем viewport
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('mb-visible');
                } else {
                    entry.target.classList.remove('mb-visible');
                }
            });
        }, observerOptions);

        // Инициализация после загрузки DOM
        document.addEventListener('DOMContentLoaded', () => {
            const items = document.querySelectorAll('.mb-icon-wrapper');
            items.forEach(item => observer.observe(item));
        });
    }
}