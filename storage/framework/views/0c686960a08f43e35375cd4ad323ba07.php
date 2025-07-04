

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Редактировать шаблон</h2>
                <a href="<?php echo e(route('admin.templates.index')); ?>" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Назад к списку
                </a>
            </div>
            
            <?php if(session('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo e(session('success')); ?>

                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if($errors->any()): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil me-2"></i>
                        Редактирование шаблона: <?php echo e($template->name); ?>

                    </h5>
                </div>
                <div class="card-body">
                    <form action="<?php echo e(route('admin.templates.update', $template)); ?>" method="POST" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Название шаблона <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="name" name="name" value="<?php echo e(old('name', $template->name)); ?>" required>
                                    <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="preview_image" class="form-label">Изображение превью</label>
                                    <input type="file" class="form-control <?php $__errorArgs = ['preview_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                           id="preview_image" name="preview_image" accept="image/*">
                                    <?php if($template->preview_image): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo e(asset('storage/' . $template->preview_image)); ?>" 
                                                 alt="Текущее превью" class="img-thumbnail" style="max-width: 100px;">
                                            <small class="text-muted d-block">Текущее изображение</small>
                                        </div>
                                    <?php endif; ?>
                                    <?php $__errorArgs = ['preview_image'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                        <div class="invalid-feedback"><?php echo e($message); ?></div>
                                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Описание</label>
                            <textarea class="form-control <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="description" name="description" rows="3"><?php echo e(old('description', $template->description)); ?></textarea>
                            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_base_template" 
                                       name="is_base_template" value="1" 
                                       <?php echo e(old('is_base_template', $template->is_base_template) ? 'checked' : ''); ?>>
                                <label class="form-check-label" for="is_base_template">
                                    <strong>Сделать базовым шаблоном</strong>
                                    <small class="text-muted d-block">Базовый шаблон будет отображаться пользователям на странице создания</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="html_content" class="form-label">HTML содержимое <span class="text-danger">*</span></label>
                            <textarea class="form-control <?php $__errorArgs = ['html_content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                                      id="html_content" name="html_content" rows="15" required><?php echo e(old('html_content', $template->html_content)); ?></textarea>
                            <?php $__errorArgs = ['html_content'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="invalid-feedback"><?php echo e($message); ?></div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            
                            <!-- Информация о редактируемых элементах -->
                            <div class="alert alert-info mt-3">
                                <h6><i class="bi bi-info-circle me-2"></i>Автоматическое обновление редактируемых элементов</h6>
                                <p class="mb-2">При сохранении система автоматически пересканирует HTML и обновит список редактируемых элементов.</p>
                                <?php if($template->editable_elements && count($template->editable_elements) > 0): ?>
                                    <p class="mb-2"><strong>Текущие редактируемые элементы (<?php echo e(count($template->editable_elements)); ?>):</strong></p>
                                    <div class="row">
                                        <?php $__currentLoopData = $template->editable_elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="col-md-6 mb-2">
                                                <small class="badge bg-light text-dark">
                                                    <?php echo e($element['field_name'] ?? $element['id']); ?> 
                                                    <span class="text-muted">(<?php echo e($element['type']); ?>)</span>
                                                </small>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                <?php else: ?>
                                    <p class="mb-0 text-warning"><i class="bi bi-exclamation-triangle me-1"></i>Редактируемые элементы не найдены. Они будут определены при сохранении.</p>
                                <?php endif; ?>
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
                                            <?php echo $template->html_content; ?>

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
                                            <li><strong>ID:</strong> <?php echo e($template->id); ?></li>
                                            <li><strong>Создан:</strong> <?php echo e($template->created_at->format('d.m.Y H:i')); ?></li>
                                            <li><strong>Обновлен:</strong> <?php echo e($template->updated_at->format('d.m.Y H:i')); ?></li>
                                            <li><strong>Статус:</strong> 
                                                <?php if($template->is_active): ?>
                                                    <span class="badge bg-success">Активен</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Неактивен</span>
                                                <?php endif; ?>
                                            </li>
                                            <li><strong>Базовый:</strong> 
                                                <?php if($template->is_base_template): ?>
                                                    <span class="badge bg-warning text-dark">Да</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Нет</span>
                                                <?php endif; ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('admin.templates.index')); ?>" class="btn btn-outline-secondary">
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
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\tyty\resources\views/admin/templates/edit.blade.php ENDPATH**/ ?>