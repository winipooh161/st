@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Информационная страница</div>

                <div class="card-body">
                    @auth
                        @if(Auth::user()->role === 'admin')
                            <div class="alert alert-info">
                                Вы просматриваете эту страницу как администратор.
                            </div>
                        @endif
                    @endauth
                    
                    <p>Эта страница доступна всем посетителям.</p>
                    <p>Зарегистрируйтесь, чтобы получить доступ к дополнительным функциям.</p>
                    
                    <!-- Добавляем контент для тестирования скролла -->
                    <div style="margin-top: 2rem;">
                        <h3>Тестирование скролла навигации</h3>
                        <p>Прокрутите страницу вниз - панель навигации должна скрыться. При прокрутке вверх - появиться снова.</p>
                        
                        @for($i = 1; $i <= 50; $i++)
                            <div class="card mt-3">
                                <div class="card-body">
                                    <h5 class="card-title">Тестовый блок {{ $i }}</h5>
                                    <p class="card-text">Это тестовый блок для проверки работы скролла. При прокрутке вниз навигационная панель должна плавно скрываться, а при прокрутке вверх - появляться снова.</p>
                                    <p class="card-text">Высота контента: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.</p>
                                </div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Добавляем отладочную информацию -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Отладка скролла навигации активна');
    
    // Добавляем слушатель для отладки
    window.addEventListener('scroll', function() {
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop % 100 < 10) { // Каждые 100px для уменьшения спама в консоли
            console.log(`📏 Scroll position: ${scrollTop}px`);
        }
    });
});
</script>
@endsection
