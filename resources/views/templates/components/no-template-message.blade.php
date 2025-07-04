{{-- Компонент для отображения сообщения, что базовый шаблон не найден --}}
<div class="no-template-message">
    <i class="bi bi-exclamation-triangle display-1 text-warning"></i>
    <h3 class="mt-3">Базовый шаблон не найден</h3>
    <p class="mb-3">Для создания пользовательских шаблонов необходимо сначала создать и настроить базовый шаблон.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">
        <i class="bi bi-house me-1"></i> На главную
    </a>
</div>
