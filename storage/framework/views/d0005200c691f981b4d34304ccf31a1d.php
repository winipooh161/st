
<div class="container-fluid template-editor-container">
    
    <?php echo $__env->make('templates.components.template-cover', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    
    <?php echo $__env->make('templates.components.template-toolbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <?php if(!isset($baseTemplate) || !$baseTemplate): ?>
        
        <?php echo $__env->make('templates.components.no-template-message', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php else: ?>
        
        <?php echo $__env->make('templates.components.template-preview', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <?php endif; ?>
</div>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/templates/components/template-editor-core.blade.php ENDPATH**/ ?>