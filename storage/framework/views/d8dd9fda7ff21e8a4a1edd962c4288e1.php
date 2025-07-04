
<div class="template-preview-container position-relative">
    

    
    <div id="template-content" class="template-content-wrapper border rounded p-3 overflow-auto">
        <?php
            // Проверяем, находимся ли мы на странице создания шаблона
            $isCreatePage = isset($baseTemplate->isCreatePage) ? $baseTemplate->isCreatePage : false;
            
            // Если находимся на странице создания, то переменная $isCreatePage будет true
            // и будет использоваться в HTML-содержимом шаблона для скрытия блока серии
        ?>
        
        <?php echo $baseTemplate->html_content ?? '<div class="alert alert-danger">Шаблон не загружен</div>'; ?>

    </div>
</div>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/templates/components/template-preview.blade.php ENDPATH**/ ?>