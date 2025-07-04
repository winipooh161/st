<style>
    /* Основные стили для редактора шаблонов */
    .template-editor-container {
        min-height: 100vh;
        display: flex;
        background: #f8f9fa;
        position: relative;
        flex-direction: column;
        overflow-x: hidden;
    }

    .template-content-wrapper {
        width: 100%;
        min-height: 100vh;
        height: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 20px;
        position: relative;
    }
    
    /* Стили для редактируемых элементов */
    .editable-element {
        border: 2px dashed transparent !important;
        cursor: text !important;
        transition: border-color .15s ease !important;
        position: relative !important;
        min-height: 20px !important;
        margin: 10px 0 !important;
    }

    /* Стили для inline редактирования */
    [data-editable] {
        border: 1px dashed transparent;
        padding: 2px 8px;
        position: relative;
        transition: all 0.2s ease;
        border-radius: 4px;
    }
    
    [data-editable]:hover {
        border-color: #007bff;
        background-color: rgba(0, 123, 255, 0.05);
    }
    
    [data-editable].editing {
        border: 1px solid #007bff;
        background-color: rgba(0, 123, 255, 0.08);
        outline: none;
        min-width: 50px;
    }
    
    [data-editable]:focus {
        outline: none;
    }
    
    /* Стили для полей даты */
    [data-field-type="date"].editing {
        cursor: pointer;
    }
    
    .flatpickr-calendar {
        z-index: 9999 !important;
    }
    
    /* Пользовательский тултип для подсказок */
    [data-editable]::before {
        content: attr(data-field-name);
        position: absolute;
        top: -25px;
        left: 0;
        background-color: #333;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 12px;
        opacity: 0;
        transition: opacity 0.2s ease;
        pointer-events: none;
        z-index: 10;
    }
    
    [data-editable]:hover::before {
        opacity: 0.9;
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        backdrop-filter: blur(2px);
    }

    @keyframes skeleton-shine {
      0% { background-position: 200% 0; }
      100% { background-position: -200% 0; }
    }

    .skeleton {
        display: inline-block;
        animation: skeleton-shine 1.2s infinite linear;
        background: linear-gradient(90deg, #f8f9fa 25%, #e9ecef 50%, #f8f9fa 75%);
        background-size: 200% 100%;
    }

    .no-template-message {
        text-align: center;
        padding: 3rem;
        color: #6c757d;
    }

    /* Стили для редактируемых элементов в iframe */
    .editable-element {
        border: 2px dashed transparent !important;
        cursor: text !important;
        transition: all 0.2s ease !important;
        position: relative !important;
        min-height: 20px !important;
    }



    /* Стили для inline редактирования */
    .inline-edit-input {
        border: none !important;
        background: transparent !important;
        outline: none !important;
        width: 100% !important;
        font-family: inherit !important;
        font-size: inherit !important;
        font-weight: inherit !important;
        color: inherit !important;
        line-height: inherit !important;
        padding: 4px !important;
        margin: 0 !important;
        resize: none !important;
    }

    .inline-edit-input:focus {
        background: rgba(255, 255, 255, 0.9) !important;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3) !important;
    }

    /* Убираем отступы для полноэкранного режима */
    .container-fluid {
        padding: 0 !important;
    }

    main.content-wrapper {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    /* Стили для обложки */
    .cover-section {
        height: 50vh;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 2px solid #e0e0e0;
        flex-direction: column;
        margin-bottom: 20px;
    }

    .cover-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #6c757d;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
    }

    .cover-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #adb5bd;
        opacity: 0.7;
    }

    .cover-text {
        font-size: 1.2rem;
        font-weight: 500;
        margin-bottom: 0.5rem;
        color: #495057;
    }

    .cover-subtext {
        font-size: 0.9rem;
        color: #6c757d;
        opacity: 0.8;
    }
  
</style>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/templates/components/template-styles.blade.php ENDPATH**/ ?>