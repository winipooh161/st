<style>
    
        .header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .company-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .date-range {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        .content-section {
            margin-bottom: 30px;
        }
        .main-content {
            font-size: 18px;
            line-height: 1.8;
            margin-bottom: 40px;
        }
        .contacts {
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 20px;
        }
        h1,h2 {
            font-size: 30px !important;
        }
        
        /* Локальные стили для редактируемых элементов
           Примечание: основные стили определены в template-styles.blade.php */
        [data-editable]:active {
            transform: scale(0.98);
        }
</style>


    <div class="company-info">
        <h2 data-editable="company_name" data-field-name="Название компании" data-field-type="text">ООО "Ваша Компания"</h2>
    </div>
    
    <div class="date-range">
        <div>
            <strong>Дата начала:</strong> 
            <span data-editable="start_date" data-field-name="Дата начала" data-field-type="date" data-date-format="dd.mm.yyyy">01.01.2025</span>
        </div>
        <div>
            <strong>Дата окончания:</strong> 
            <span data-editable="end_date" data-field-name="Дата окончания" data-field-type="date" data-date-format="dd.mm.yyyy">31.12.2025</span>
        </div>
    </div>
    
    <div class="content-section">
        <h3 data-editable="rezum" data-field-name="Заголовок Резюме" data-field-type="text">Резюме</h3>
        <p data-editable="summary" data-field-name="Резюме" data-field-type="text">
            Здесь представлен пример редактируемого текста в шаблоне документа. Вы можете нажать на этот текст, чтобы отредактировать его. После внесения изменений нажмите Enter или кликните в любом другом месте для сохранения.
        </p>
    </div>
    
    <div class="main-content">
        <h3 data-editable="main_title" data-field-name="Заголовок Основного Содержимого" data-field-type="text">Основное содержимое</h3>
        <p data-editable="main_content" data-field-name="Основное содержимое" data-field-type="text">
            Данный шаблон демонстрирует возможности прямого редактирования текста в документе. Вы можете изменять текст просто кликнув по нему. Также поддерживается редактирование дат с помощью календаря.
        </p>
        <p data-editable="additional_content" data-field-name="Дополнительное содержимое" data-field-type="text">
            Все изменения автоматически сохраняются и синхронизируются с сервером. Это позволяет легко создавать и редактировать шаблоны документов без необходимости работы с отдельными формами.
        </p>
        
       
    </div>
    
    <div class="contacts">
        <h4>Контакты</h4>
        <p>Телефон: <span data-editable="phone" data-field-name="Телефон" data-field-type="text">+7 (999) 123-45-67</span></p>
        <p>Email: <span data-editable="email" data-field-name="Email" data-field-type="text">example@example.com</span></p>
        <p>Адрес: <span data-editable="address" data-field-name="Адрес" data-field-type="text">г. Москва, ул. Примерная, д. 123</span></p>
        
    </div>
