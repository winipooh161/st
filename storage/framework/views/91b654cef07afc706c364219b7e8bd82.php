
<div class="template-toolbar">
    <div class="toolbar-left">
        <h4 class="mb-0"><?php echo e(isset($baseTemplate) ? $baseTemplate->name : 'Создание нового шаблона'); ?></h4>
        
        <input type="hidden" id="template-name" value="<?php echo e(isset($baseTemplate) && $baseTemplate->name ? $baseTemplate->name : 'Мой шаблон ' . now()->format('d.m.Y')); ?>">
    </div>
    <div class="toolbar-right">
        <button type="button" class="template-series-btn me-2" data-bs-toggle="modal" data-bs-target="#seriesModal">
            <i class="fas fa-layer-group me-1"></i> Серия
        </button>
        <button id="save-template-btn" class="template-save-btn" onclick="window.TemplateEditor && window.TemplateEditor.saveTemplateData()">
            <i class="fas fa-save mr-1"></i> Сохранить
        </button>
    </div>
</div>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/templates/components/template-toolbar.blade.php ENDPATH**/ ?>