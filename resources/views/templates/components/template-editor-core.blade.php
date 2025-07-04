{{-- Основной компонент редактора шаблонов --}}
<div class="container-fluid template-editor-container">
    {{-- Секция обложки --}}
    @include('templates.components.template-cover')

    {{-- Панель инструментов --}}
    @include('templates.components.template-toolbar')

    @if(!isset($baseTemplate) || !$baseTemplate)
        {{-- Сообщение об отсутствии шаблона --}}
        @include('templates.components.no-template-message')
    @else
        {{-- Блок предварительного просмотра с загрузочным оверлеем --}}
        @include('templates.components.template-preview')
    @endif
</div>
