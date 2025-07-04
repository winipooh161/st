@extends('layouts.app')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/user-template-editor.css') }}">
@endsection

@section('content')
<div class="template-editor-container">
    <!-- Секция обложки шаблона -->
    <div class="template-cover-section" style="height: 75vh; width: 100%; position: relative;">
        @if($mediaType == 'image')
        <div class="template-cover-image" style="height: 100%; width: 100%; overflow: hidden;">
            <img src="{{ $mediaUrl }}" alt="Обложка шаблона" class="cover-media" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        @elseif($mediaType == 'video')
        <div class="template-cover-video" style="height: 100%; width: 100%; overflow: hidden;">
            <video src="{{ $mediaUrl }}" class="cover-media" autoplay muted loop style="width: 100%; height: 100%; object-fit: cover;">
                <source src="{{ $mediaUrl }}" type="video/mp4">
                Ваш браузер не поддерживает видео.
            </video>
            @if($thumbnailUrl)
            <div class="template-thumbnail" style="display: none;">
                <img src="{{ $thumbnailUrl }}" alt="Миниатюра видео">
            </div>
            @endif
        </div>
        @endif
        
        <div class="template-cover-overlay" style="position: absolute; bottom: 0; left: 0; right: 0; padding: 2rem; background: linear-gradient(transparent, rgba(0,0,0,0.7));">
            <div class="container">
                <h1 class="template-title" style="color: white; text-shadow: 1px 1px 3px rgba(0,0,0,0.5);">Мой шаблон</h1>
            </div>
        </div>
    </div>

    <!-- Шаблон (без формы редактирования) -->
    <div class="template-content-section">
        <form id="template-form" action="{{ route('user.templates.store') }}" method="POST" style="display: none;">
            @csrf
            <input type="hidden" name="template_id" value="{{ $baseTemplate->id }}">
            <input type="hidden" name="name" id="hidden_name" value="Мой шаблон">
            <input type="hidden" id="html_content" name="html_content" value="{{ $baseTemplate->html_content }}">
            <input type="hidden" id="custom_data" name="custom_data" value="{}">
            <input type="hidden" id="is_published" name="is_published" value="1">
        </form>

        <div class="template-preview-section py-5">
            <div class="container">
                <!-- Живой шаблон -->
                <div class="template-container">
                    {!! $baseTemplate->html_content !!}
                </div>
                
                <!-- Кнопки управления -->
                <div class="action-buttons mt-4 text-center">
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ route('media.editor') }}" class="btn btn-outline-secondary w-100">
                                <i class="bi bi-arrow-left me-2"></i>Изменить обложку
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="button" id="save-template-btn" class="btn btn-primary w-100">
                                <i class="bi bi-check-lg me-2"></i>Сохранить шаблон
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Индикатор загрузки -->
<div class="loading-spinner-overlay" id="form-submit-spinner" style="display: none;">
    <div class="loading-spinner-container">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Загрузка...</span>
        </div>
        <p class="mt-3">Сохранение шаблона...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Обработка кнопки сохранения
        const saveTemplateBtn = document.getElementById('save-template-btn');
        const form = document.getElementById('template-form');
        
        if (saveTemplateBtn && form) {
            saveTemplateBtn.addEventListener('click', function() {
                // Показываем индикатор загрузки
                document.getElementById('form-submit-spinner').style.display = 'flex';
                
                // Можно запросить название шаблона или использовать значение по умолчанию
                const templateName = prompt('Введите название для вашего шаблона:', 'Мой шаблон');
                
                if (templateName) {
                    // Обновляем скрытое поле с именем шаблона
                    document.getElementById('hidden_name').value = templateName;
                    
                    // Отправляем форму
                    form.submit();
                } else {
                    // Скрываем индикатор, если пользователь отменил
                    document.getElementById('form-submit-spinner').style.display = 'none';
                }
            });
        }
    });
</script>
@endsection
