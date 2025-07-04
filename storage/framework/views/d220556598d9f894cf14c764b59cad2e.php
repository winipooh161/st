

<?php $__env->startSection('scripts'); ?>
    <script src="<?php echo e(asset('js/media-editor.js')); ?>"></script>
    <script src="<?php echo e(asset('js/custom-media-editor.js')); ?>"></script>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="media-editor-container fullscreen-editor">

   
        
        <!-- Секция загрузки файла -->
        <div id="uploadSection" class="text-center py-5">
            <div id="dropZone" class="drop-zone mb-4">
                <i class="bi bi-cloud-arrow-up-fill display-1 text-primary mb-3"></i>
                <h3>Перетащите изображение сюда или нажмите для выбора</h3>
                <p class="text-muted">Поддерживаются только изображения (JPG, PNG, GIF). Видеоредактор временно отключен.</p>
            </div>
            
            <button id="uploadBtn" class="btn btn-primary btn-lg">
                <i class="bi bi-upload me-2"></i>Выбрать файл
            </button>
            <input type="file" id="mediaFile" accept="image/jpeg,image/png,image/gif" style="display: none;">
            
            <!-- Для передачи дополнительных параметров -->
            <?php if(request()->has('template_id')): ?>
                <input type="hidden" id="templateId" name="template_id" value="<?php echo e(request('template_id')); ?>">
            <?php endif; ?>
            
            <?php if(request()->has('section')): ?>
                <input type="hidden" id="templateSection" name="template_section" value="<?php echo e(request('section')); ?>">
            <?php endif; ?>
            
            <?php if(request()->has('return_url')): ?>
                <input type="hidden" id="returnUrl" name="return_url" value="<?php echo e(request('return_url')); ?>">
            <?php endif; ?>
        </div>
        
        <!-- Секция редактора изображений -->
        <div id="imageEditorSection" class="py-3" style="display: none;">
            <div id="imageViewport" class="image-viewport reels-format mb-3">
                <img id="imagePreview" class="img-fluid" src="" alt="Предварительный просмотр">
                <div class="image-viewport-hint">Область внутри рамки будет сохранена</div>
            </div>
            <div class="text-center text-muted mt-2 mb-4">
                <small>
                    <i class="bi bi-info-circle me-1"></i>
                    Используйте мышь для перемещения и колесико для масштабирования изображения.
                    На мобильных устройствах используйте жесты растяжения и перетаскивания.
                </small>
                <div class="crop-info d-none">
                    <div>
                        <strong>Размеры Viewport:</strong> <span id="viewport-size"></span>
                    </div>
                    <div>
                        <strong>Размеры Изображения:</strong> <span id="image-size"></span>
                    </div>
                    <div>
                        <strong>Масштаб:</strong> <span id="image-scale"></span>
                    </div>
                    <div>
                        <strong>Смещение:</strong> <span id="image-offset"></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Секция редактора видео отключена - поддерживаются только изображения -->
        <div id="videoEditorSection" class="py-3" style="display: none;">
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Редактирование видео временно отключено. Пожалуйста, используйте только фоторедактор.
            </div>
        </div>

        <!-- Индикатор обработки -->
        <div id="processingIndicator" class="processing-overlay" style="display: none;">
            <div class="processing-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Загрузка...</span>
                </div>
                <p class="mt-3">Обработка файла...</p>
            </div>
        </div>
        
        <!-- Контейнер для сообщений -->
        <div id="statusMessage" class="alert" style="display: none;"></div>
   
</div>

<!-- Подключаем стили и скрипты -->
<link rel="stylesheet" href="<?php echo e(asset('css/media-editor.css')); ?>">
<?php echo app('Illuminate\Foundation\Vite')(['resources/css/media-editor.css']); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\OSPanel\domains\tyty\resources\views/media/editor.blade.php ENDPATH**/ ?>