
<div class="cover-section">
    <div id="coverContainer" class="cover-placeholder">
        <?php if(session('media_editor_file')): ?>
            <?php if(session('media_editor_type') == 'image'): ?>
                <img src="<?php echo e(Storage::url(session('media_editor_file'))); ?>" alt="Обложка" class="img-fluid">
            <?php elseif(session('media_editor_type') == 'video'): ?>
                <video class="w-100" autoplay loop muted playsinline>
                    <source src="<?php echo e(Storage::url(session('media_editor_file'))); ?>" type="video/mp4">
                </video>
                <?php if(session('media_editor_thumbnail')): ?>
                    <input type="hidden" id="coverThumbnail" value="<?php echo e(session('media_editor_thumbnail')); ?>">
                <?php endif; ?>
            <?php endif; ?>
            <input type="hidden" id="coverPath" value="<?php echo e(session('media_editor_file')); ?>">
            <input type="hidden" id="coverType" value="<?php echo e(session('media_editor_type')); ?>">
        <?php else: ?>
            <div class="cover-icon">
                <i class="fas fa-image"></i>
            </div>
            <div class="cover-text">Место для обложки</div>
            <div class="cover-subtext">Нажмите, чтобы выбрать обложку</div>
            
            <input type="hidden" id="coverPath" value="">
            <input type="hidden" id="coverType" value="">
            <input type="hidden" id="coverThumbnail" value="">
        <?php endif; ?>
    </div>
</div>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/templates/components/template-cover.blade.php ENDPATH**/ ?>