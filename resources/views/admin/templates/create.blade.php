@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Добавить новый шаблон</h2>
                <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Назад к списку
                </a>
            </div>
            
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-plus me-2"></i>
                        Создание нового шаблона
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название шаблона <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="preview_image" class="form-label">Изображение превью</label>
                                    <input type="file" class="form-control @error('preview_image') is-invalid @enderror" 
                                           id="preview_image" name="preview_image" accept="image/*">
                                    @error('preview_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_base_template" 
                                       name="is_base_template" value="1" {{ old('is_base_template') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_base_template">
                                    <strong>Сделать базовым шаблоном</strong>
                                    <small class="text-muted d-block">Базовый шаблон будет отображаться пользователям на странице создания</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="html_content" class="form-label">HTML содержимое <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('html_content') is-invalid @enderror" 
                                      id="html_content" name="html_content" rows="15" required>{{ old('html_content', '<div class="template-container">
    <h1>Мой шаблон</h1>
    <p>Содержимое шаблона...</p>
</div>

<style>
.template-container {
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    text-align: center;
}
</style>') }}</textarea>
                            @error('html_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Информация о редактируемых элементах -->
                            <div class="alert alert-info mt-3">
                                <h6><i class="bi bi-info-circle me-2"></i>Автоматическое определение редактируемых элементов</h6>
                                <p class="mb-2">При создании шаблона система автоматически найдёт и пометит редактируемые элементы:</p>
                                <ul class="mb-2">
                                    <li><strong>Элементы с атрибутом</strong> <code>data-editable="true"</code> - приоритетный способ</li>
                                    <li><strong>Заголовки</strong> (h1-h6) с текстом</li>
                                    <li><strong>Параграфы</strong> (p) с содержимым</li>
                                    <li><strong>Изображения</strong> (img) с src атрибутом</li>
                                    <li><strong>Ссылки</strong> (a) с href и текстом</li>
                                    <li><strong>Элементы с</strong> <code>contenteditable="true"</code></li>
                                </ul>
                                <p class="mb-0"><strong>Совет:</strong> Используйте <code>data-editable="true"</code>, <code>data-field-type="text|image|link|email|tel"</code> и <code>data-field-name="название_поля"</code> для лучшего контроля.</p>
                            </div>
                        </div>
                     
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Создать шаблон
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const htmlContent = document.getElementById('html_content');
    const previewContainer = document.getElementById('preview-container');
    
    function updatePreview() {
        const html = htmlContent.value;
        if (html.trim()) {
            previewContainer.innerHTML = html;
        } else {
            previewContainer.innerHTML = '<div class="d-flex align-items-center justify-content-center h-100 text-muted">Введите HTML код для предварительного просмотра</div>';
        }
    }
    
    // Обновляем превью при вводе
    htmlContent.addEventListener('input', updatePreview);
    
    // Инициализируем превью
    updatePreview();
});
</script>
@endsection
