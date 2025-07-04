@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Редактировать шаблон</h2>
                <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Назад к списку
                </a>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
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
                        <i class="bi bi-pencil me-2"></i>
                        Редактирование шаблона: {{ $template->name }}
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.templates.update', $template) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название шаблона <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $template->name) }}" required>
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
                                    @if($template->preview_image)
                                        <div class="mt-2">
                                            <img src="{{ asset('storage/' . $template->preview_image) }}" 
                                                 alt="Текущее превью" class="img-thumbnail" style="max-width: 100px;">
                                            <small class="text-muted d-block">Текущее изображение</small>
                                        </div>
                                    @endif
                                    @error('preview_image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $template->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_base_template" 
                                       name="is_base_template" value="1" 
                                       {{ old('is_base_template', $template->is_base_template) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_base_template">
                                    <strong>Сделать базовым шаблоном</strong>
                                    <small class="text-muted d-block">Базовый шаблон будет отображаться пользователям на странице создания</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="html_content" class="form-label">HTML содержимое <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('html_content') is-invalid @enderror" 
                                      id="html_content" name="html_content" rows="15" required>{{ old('html_content', $template->html_content) }}</textarea>
                            @error('html_content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Информация о редактируемых элементах -->
                            <div class="alert alert-info mt-3">
                                <h6><i class="bi bi-info-circle me-2"></i>Автоматическое обновление редактируемых элементов</h6>
                                <p class="mb-2">При сохранении система автоматически пересканирует HTML и обновит список редактируемых элементов.</p>
                                @if($template->editable_elements && count($template->editable_elements) > 0)
                                    <p class="mb-2"><strong>Текущие редактируемые элементы ({{ count($template->editable_elements) }}):</strong></p>
                                    <div class="row">
                                        @foreach($template->editable_elements as $element)
                                            <div class="col-md-6 mb-2">
                                                <small class="badge bg-light text-dark">
                                                    {{ $element['field_name'] ?? $element['id'] }} 
                                                    <span class="text-muted">({{ $element['type'] }})</span>
                                                </small>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mb-0 text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Редактируемые элементы не найдены. Они будут определены при сохранении.</p>
                                @endif
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Предварительный просмотр</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="preview-container" style="border: 1px solid #dee2e6; min-height: 200px; background: #fff;">
                                            {!! $template->html_content !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Информация о шаблоне</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-unstyled small">
                                            <li><strong>ID:</strong> {{ $template->id }}</li>
                                            <li><strong>Создан:</strong> {{ $template->created_at->format('d.m.Y H:i') }}</li>
                                            <li><strong>Обновлен:</strong> {{ $template->updated_at->format('d.m.Y H:i') }}</li>
                                            <li><strong>Статус:</strong> 
                                                @if($template->is_active)
                                                    <span class="badge bg-success">Активен</span>
                                                @else
                                                    <span class="badge bg-danger">Неактивен</span>
                                                @endif
                                            </li>
                                            <li><strong>Базовый:</strong> 
                                                @if($template->is_base_template)
                                                    <span class="badge bg-warning text-dark">Да</span>
                                                @else
                                                    <span class="badge bg-secondary">Нет</span>
                                                @endif
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Сохранить изменения
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
});
</script>
@endsection
