<nav class="mb-navigation mb-dock hide-desktop">
    <!-- Добавляем безопасную зону для защиты от системных жестов -->
    <div class="mb-gesture-protection"></div>
    
    <div class="mb-fixed-container">
        <div class="mb-scroller" id="nav-scroll-container">
            <div class="mb-icons-container" id="nav-icons-container">
                @if(request()->is('media/editor*') || request()->is('templates/create*') || request()->is('templates/editor*'))
                    <div class="mb-icon-wrapper" data-icon-id="back">
                        <a href="{{ url()->previous() }}" class="mb-nav-link">
                            <div class="mb-nav-icon-wrap">
                                <img src="{{ asset('images/icons/arrow-left.svg') }}" class="mb-nav-icon" alt="Назад" draggable="false">
                            </div>
                        </a>
                    </div>
                    <div class="mb-icon-wrapper" data-icon-id="home">
                        <a href="{{ route('home') }}" class="mb-nav-link {{ request()->routeIs('home') ? 'mb-active' : '' }}">
                            <div class="mb-nav-icon-wrap">
                                <img src="{{ asset('images/center-icon.svg') }}" class="mb-nav-icon" alt="Главная" draggable="false">
                            </div>
                        </a>
                    </div>
                   
                    
                 
                    @if(request()->is('media/editor*'))
                        <div class="mb-icon-wrapper" data-icon-id="save" data-popup-on-click="false">
                            <a href="#" class="mb-nav-link" id="save-media-btn">
                                <div class="mb-nav-icon-wrap">
                                    <img src="{{ asset('images/icons/save.svg') }}" class="mb-nav-icon" alt="Сохранить" draggable="false">
                                </div>
                            </a>
                        </div>
                    @elseif(request()->is('templates/create*') || request()->is('templates/editor*'))
                        <div class="mb-icon-wrapper" data-icon-id="save" data-popup-on-click="false">
                            <a href="#" class="mb-nav-link" id="save-media-btn" onclick="return false;">
                                <div class="mb-nav-icon-wrap">
                                    <img src="{{ asset('images/icons/save.svg') }}" class="mb-nav-icon" alt="Сохранить" draggable="false">
                                </div>
                            </a>
                        </div>
                    @endif
                @else
                    <div class="mb-icon-wrapper" data-icon-id="qr-scanner" data-modal="true" data-modal-target="qrScannerModal" data-popup-on-click="false">
                        <a href="#" class="mb-nav-link">
                            <div class="mb-nav-icon-wrap">
                                <img src="{{ asset('images/icons/qr-code.svg') }}" class="mb-nav-icon" alt="QR-сканер" draggable="false">
                            </div>
                        </a>
                    </div>
                    <div class="mb-icon-wrapper" data-icon-id="home" data-popup-on-click="false">
                        <a href="{{ route('home') }}" class="mb-nav-link {{ request()->routeIs('home') ? 'mb-active' : '' }}">
                            <div class="mb-nav-icon-wrap">
                                <img src="{{ asset('images/center-icon.svg') }}" class="mb-nav-icon" alt="Главная" draggable="false">
                            </div>
                        </a>
                    </div>
                      <!-- Новая иконка для шаблонов с выпадающим меню -->
                <div class="mb-icon-wrapper" data-icon-id="templates" data-popup-on-click="true">
                    <a href="{{ Auth::check() ? route('my-templates') : route('public-templates') }}" class="mb-nav-link {{ request()->routeIs('my-templates', 'public-templates') ? 'mb-active' : '' }}">
                        <div class="mb-nav-icon-wrap">
                                <img src="{{ asset('images/icons/person.svg') }}" class="mb-nav-icon" alt="Шаблоны" draggable="false">
                            </div>
                    </a>
                </div>
                    <div class="mb-icon-wrapper" data-icon-id="create" data-popup-on-click="false">
                        <a href="{{ route('media.editor') }}" class="mb-nav-link {{ request()->routeIs('media.editor') ? 'mb-active' : '' }}">
                            <div class="mb-nav-icon-wrap">
                                <img src="{{ asset('images/icons/save.svg') }}" class="mb-nav-icon" alt="Создать" draggable="false">
                            </div>
                        </a>
                    </div>
                @endif

              
            </div>
        </div>
    </div>
</nav>

<!-- Скрытый элемент для совместимости с существующим кодом -->
<div style="display: none;">
    <div class="action-buttons position-fixed bottom-0 start-0 end-0 p-3 bg-white shadow-lg" id="actionButtons">
        <div class="row">
            <div class="col-12">
                <button type="button" class="btn btn-success btn-lg w-100" id="saveBtn">
                    <i class="bi bi-check-lg me-2"></i>Готово
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Загрузка модулей навигации через Vite -->
@vite(['resources/js/mobile-nav-wheel-picker.js'])

<script>
// Дополнительная инициализация для мобильной навигации
document.addEventListener('DOMContentLoaded', function() {
    const mbNavigation = document.querySelector('.mb-navigation');
    if (mbNavigation) {
        mbNavigation.classList.add('mb-nav-loaded');
        mbNavigation.classList.remove('mb-nav-hidden');
        mbNavigation.style.display = 'flex';
        mbNavigation.style.opacity = '1';
        mbNavigation.style.transform = 'translateY(0)';
        
        console.log('Мобильная навигация инициализирована');
    } else {
        console.warn('⚠️ Мобильная навигация не найдена на странице');
    }
    
    // Настройка предотвращения перетаскивания
    preventDragOnNavElements();
});

/**
 * Настройка предотвращения перетаскивания элементов навигации
 */
function preventDragOnNavElements() {
    document.querySelectorAll('.mb-nav-icon, .mb-icon-wrapper').forEach(element => {
        element.style.setProperty('-webkit-user-drag', 'none', 'important');
        element.style.setProperty('-khtml-user-drag', 'none', 'important');
        element.style.setProperty('-moz-user-drag', 'none', 'important');
        element.style.setProperty('-o-user-drag', 'none', 'important');
        element.style.setProperty('user-drag', 'none', 'important');
        element.draggable = false;
        
        element.addEventListener('dragstart', function(e) {
            e.preventDefault();
            return false;
        });
        
        element.addEventListener('selectstart', function(e) {
            if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();
                return false;
            }
        });
    });
}
</script>

<!-- Подключаем оптимизированные стили для мобильной навигации -->
<link rel="stylesheet" href="{{ asset('css/mobile-nav-optimized.css') }}">

<!-- Добавляем дополнительный скрипт для обработки кнопок на страницах шаблонов -->
@if(request()->is('templates/create*') || request()->is('templates/editor*'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Находим кнопку сохранения шаблона
        const saveTemplateBtn = document.getElementById('save-template-btn');
        
        if (saveTemplateBtn) {
            console.log('🔄 Добавление прямого обработчика для кнопки сохранения шаблона');
            
            // Добавляем обработчик для касания (мобильные устройства)
            saveTemplateBtn.addEventListener('touchend', function(e) {
                console.log('👆 Касание на кнопке сохранения шаблона');
                e.preventDefault();
                
                // Проверяем, есть ли модальное окно серии
                const seriesModal = document.getElementById('seriesModal');
                if (seriesModal && typeof bootstrap !== 'undefined') {
                    // Показываем модальное окно для настройки серии
                    console.log('📋 Открываем модальное окно серии');
                    const modal = new bootstrap.Modal(seriesModal);
                    modal.show();
                } else if (window.saveTemplateData) {
                    console.log('⚙️ Вызов функции сохранения шаблона напрямую');
                    window.saveTemplateData();
                } else {
                    console.error('❌ Функция saveTemplateData не найдена');
                }
            });
            
            // Добавляем обработчик для клика (десктоп)
            saveTemplateBtn.addEventListener('click', function(e) {
                console.log('🖱️ Клик на кнопке сохранения шаблона');
                e.preventDefault();
                
                // Проверяем, есть ли модальное окно серии
                const seriesModal = document.getElementById('seriesModal');
                if (seriesModal && typeof bootstrap !== 'undefined') {
                    // Показываем модальное окно для настройки серии
                    console.log('📋 Открываем модальное окно серии');
                    const modal = new bootstrap.Modal(seriesModal);
                    modal.show();
                } else if (window.saveTemplateData) {
                    console.log('⚙️ Вызов функции сохранения шаблона напрямую');
                    window.saveTemplateData();
                } else {
                    console.error('❌ Функция saveTemplateData не найдена');
                }
            });
        } else {
            console.warn('⚠️ Кнопка сохранения шаблона не найдена');
        }
    });
</script>
@endif

@if(request()->is('media/editor*'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Находим кнопку сохранения медиа
        const saveMediaBtn = document.getElementById('save-media-btn');
        
        if (saveMediaBtn) {
            console.log('🔄 Добавление обработчика для кнопки сохранения медиа');
            
            // Сохраняем исходный обработчик, если он есть
            const originalOnclick = saveMediaBtn.onclick;
            
            // Добавляем обработчики событий
            saveMediaBtn.addEventListener('click', handleSaveMedia);
            saveMediaBtn.addEventListener('touchend', handleSaveMedia);
            
            function handleSaveMedia(e) {
                console.log('👆 Нажатие на кнопке сохранения медиа');
                e.preventDefault();
                
                // Если есть исходный обработчик, вызываем его
                if (originalOnclick) {
                    originalOnclick.call(this, e);
                }
                
                // Иначе просто предупреждаем пользователя
                console.log('⚙️ Обработчик сохранения медиа');
            }
        } else {
            console.warn('⚠️ Кнопка сохранения медиа не найдена');
        }
    });
</script>
@endif
