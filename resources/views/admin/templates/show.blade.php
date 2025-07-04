@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Просмотр шаблона</h2>
                <div>
                    <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary me-2">
                        <i class="bi bi-pencil me-2"></i>Редактировать
                    </a>
                    <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Назад к списку
                    </a>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-eye me-2"></i>
                                {{ $template->name }}
                                @if($template->is_base_template)
                                    <span class="badge bg-warning text-dark ms-2">Базовый шаблон</span>
                                @endif
                            </h5>
                        </div>
                        <div class="card-body">
                            <div style="border: 1px solid #dee2e6; min-height: 400px; background: #fff; overflow: auto;">
                                {!! $template->html_content !!}
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">HTML код</h6>
                        </div>
                        <div class="card-body">
                            <pre><code class="language-html">{{ $template->html_content }}</code></pre>
                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="copyToClipboard()">
                                <i class="bi bi-clipboard me-1"></i>Скопировать код
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Информация о шаблоне</h6>
                        </div>
                        <div class="card-body">
                            @if($template->preview_image)
                                <div class="mb-3">
                                    <label class="form-label">Превью:</label>
                                    <img src="{{ asset('storage/' . $template->preview_image) }}" 
                                         alt="Превью шаблона" class="img-fluid rounded">
                                </div>
                            @endif
                            
                            <div class="mb-2">
                                <strong>ID:</strong> {{ $template->id }}
                            </div>
                            
                            <div class="mb-2">
                                <strong>Название:</strong> {{ $template->name }}
                            </div>
                            
                            @if($template->description)
                                <div class="mb-2">
                                    <strong>Описание:</strong><br>
                                    {{ $template->description }}
                                </div>
                            @endif
                            
                            <div class="mb-2">
                                <strong>Статус:</strong>
                                @if($template->is_active)
                                    <span class="badge bg-success">Активен</span>
                                @else
                                    <span class="badge bg-danger">Неактивен</span>
                                @endif
                            </div>
                            
                            <div class="mb-2">
                                <strong>Базовый шаблон:</strong>
                                @if($template->is_base_template)
                                    <span class="badge bg-warning text-dark">Да</span>
                                @else
                                    <span class="badge bg-secondary">Нет</span>
                                @endif
                            </div>
                            
                            <div class="mb-2">
                                <strong>Создан:</strong><br>
                                {{ $template->created_at->format('d.m.Y в H:i') }}
                            </div>
                            
                            <div class="mb-2">
                                <strong>Обновлен:</strong><br>
                                {{ $template->updated_at->format('d.m.Y в H:i') }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-header">
                            <h6 class="mb-0">Действия</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil me-2"></i>Редактировать
                                </a>
                                
                                @if(!$template->is_base_template)
                                    <form action="{{ route('admin.templates.toggle-base', $template) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="bi bi-star me-2"></i>Сделать базовым
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.templates.toggle-base', $template) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-warning w-100">
                                            <i class="bi bi-star-fill me-2"></i>Убрать из базовых
                                        </button>
                                    </form>
                                @endif
                                
                                <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $template->id }})">
                                    <i class="bi bi-trash me-2"></i>Удалить
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно подтверждения удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Подтверждение удаления</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы уверены, что хотите удалить этот шаблон?</p>
                <p class="text-danger"><small>Это действие необратимо!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Удалить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function confirmDelete(templateId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/admin/templates/${templateId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function copyToClipboard() {
    const code = document.querySelector('pre code').textContent;
    navigator.clipboard.writeText(code).then(function() {
        // Создаем временное уведомление
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check me-1"></i>Скопировано!';
        btn.classList.remove('btn-outline-secondary');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-secondary');
        }, 2000);
    });
}
</script>
@endsection
