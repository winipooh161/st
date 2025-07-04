@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Управление шаблонами</h2>
                <div>
                    <a href="{{ route('admin.templates.create') }}" class="btn btn-primary me-2">
                        <i class="bi bi-plus-lg me-2"></i>Добавить шаблон
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-2"></i>Назад к панели
                    </a>
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Список шаблонов ({{ $templates->count() }})</h5>
                </div>
                <div class="card-body">
                    @if($templates->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Превью</th>
                                        <th>Название</th>
                                        <th>Описание</th>
                                        <th>Базовый</th>
                                        <th>Статус</th>
                                        <th>Элементы</th>
                                        <th>Дата создания</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($templates as $template)
                                    <tr>
                                        <td>{{ $template->id }}</td>
                                        <td>
                                            @if($template->preview_image)
                                                <img src="{{ asset('storage/' . $template->preview_image) }}" 
                                                     alt="Превью" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                            @else
                                                <div class="bg-light border rounded d-flex align-items-center justify-content-center" 
                                                     style="width: 50px; height: 50px;">
                                                    <i class="bi bi-image text-muted"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ $template->name }}</strong>
                                            @if($template->is_base_template)
                                                <span class="badge bg-warning text-dark ms-1">Базовый</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->description)
                                                {{ Str::limit($template->description, 50) }}
                                            @else
                                                <span class="text-muted">Нет описания</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->is_base_template)
                                                <span class="badge bg-success">Да</span>
                                            @else
                                                <span class="badge bg-secondary">Нет</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->is_active)
                                                <span class="badge bg-success">Активен</span>
                                            @else
                                                <span class="badge bg-danger">Неактивен</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($template->editable_elements)
                                                <span class="badge bg-info">{{ count($template->editable_elements) }}</span>
                                                <small class="text-muted d-block">элементов</small>
                                            @else
                                                <span class="text-muted">Не анализировался</span>
                                            @endif
                                        </td>
                                        <td>{{ $template->created_at->format('d.m.Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.templates.show', $template) }}" 
                                                   class="btn btn-sm btn-outline-info" title="Просмотр">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.templates.edit', $template) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Редактировать">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                @if(!$template->is_base_template)
                                                    <form action="{{ route('admin.templates.toggle-base', $template) }}" 
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                title="Сделать базовым">
                                                            <i class="bi bi-star"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.templates.toggle-base', $template) }}" 
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-warning" 
                                                                title="Убрать из базовых">
                                                            <i class="bi bi-star-fill"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                <button type="button" class="btn btn-sm btn-outline-success" 
                                                        onclick="analyzeTemplate({{ $template->id }})" title="Анализировать редактируемые элементы">
                                                    <i class="bi bi-search"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmDelete({{ $template->id }})" title="Удалить">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-file-earmark-text display-1 text-muted"></i>
                            <h5 class="mt-3">Шаблоны не найдены</h5>
                            <p class="text-muted">Добавьте первый шаблон для отображения здесь.</p>
                            <a href="{{ route('admin.templates.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg me-2"></i>Добавить шаблон
                            </a>
                        </div>
                    @endif
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

function analyzeTemplate(templateId) {
    // Показываем индикатор загрузки
    const btn = event.target.closest('button');
    const originalContent = btn.innerHTML;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm" role="status"></i>';
    btn.disabled = true;
    
    // Отправляем AJAX запрос на анализ шаблона
    fetch(`/admin/templates/${templateId}/analyze`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Показываем успешное сообщение
            showNotification('success', `Найдено ${data.elements_count} редактируемых элементов!`);
            
            // Опционально - перезагрузить страницу или обновить строку таблицы
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message || 'Ошибка при анализе шаблона');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Произошла ошибка при анализе шаблона');
    })
    .finally(() => {
        // Восстанавливаем кнопку
        btn.innerHTML = originalContent;
        btn.disabled = false;
    });
}

function showNotification(type, message) {
    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Автоматически удаляем через 5 секунд
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}
</script>
@endsection
