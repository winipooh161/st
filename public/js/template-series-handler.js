/**
 * Скрипты для работы с сериями шаблонов
 */
document.addEventListener('DOMContentLoaded', function() {
    // Элементы управления серией
    const isSeriesCheckbox = document.getElementById('isSeriesCheckbox');
    const seriesSettingsContainer = document.getElementById('seriesSettingsContainer');
    const seriesMaxInput = document.getElementById('seriesMaxInput');
    const saveSeriesSettingsBtn = document.getElementById('saveSeriesSettings');
    const seriesModal = document.getElementById('seriesModal');
    
    // Сохраняем настройки серии в глобальной области видимости
    window.seriesSettings = {
        isSeries: false,
        seriesMax: 10,
        seriesCurrent: 0
    };
    
    // Обработчик изменения статуса чекбокса
    if (isSeriesCheckbox) {
        isSeriesCheckbox.addEventListener('change', function() {
            console.log('Изменение статуса серии:', this.checked);
            seriesSettingsContainer.style.display = this.checked ? 'block' : 'none';
            window.seriesSettings.isSeries = this.checked;
            
            if (!this.checked) {
                // Сбрасываем значения при отключении серии
                seriesMaxInput.value = 10;
                window.seriesSettings.seriesMax = 10;
            }
        });
    }
    
    // Обработчик изменения максимального значения серии
    if (seriesMaxInput) {
        seriesMaxInput.addEventListener('change', function() {
            let value = parseInt(this.value);
            if (isNaN(value) || value < 1) {
                value = 1;
                this.value = 1;
            }
            console.log('Установка максимального значения серии:', value);
            window.seriesSettings.seriesMax = value;
        });
    }
    
    // Сохранение настроек серии и закрытие модального окна
    if (saveSeriesSettingsBtn) {
        // Удаляем все существующие обработчики
        const newSaveBtn = saveSeriesSettingsBtn.cloneNode(true);
        saveSeriesSettingsBtn.parentNode.replaceChild(newSaveBtn, saveSeriesSettingsBtn);
        
        // Добавляем единственный обработчик
        newSaveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Блокируем кнопку чтобы предотвратить повторные нажатия
            this.disabled = true;
            
            console.log('Сохранение шаблона с настройками серии');
            
            // Обновляем глобальные настройки серии
            window.seriesSettings.isSeries = isSeriesCheckbox.checked;
            window.seriesSettings.seriesMax = parseInt(seriesMaxInput.value) || 10;
            window.seriesSettings.seriesCurrent = 0; // Для нового шаблона всегда 0
            
            // Добавляем также поля в формате API для совместимости
            window.seriesSettings.is_series = isSeriesCheckbox.checked;
            window.seriesSettings.series_max = parseInt(seriesMaxInput.value) || 10;
            window.seriesSettings.series_current = 0;
            
            console.log('Итоговые настройки серии:', window.seriesSettings);
            
            // Закрываем модальное окно
            const modalInstance = bootstrap.Modal.getInstance(seriesModal);
            if (modalInstance) {
                modalInstance.hide();
            }
            
            // Вызываем функцию сохранения с текущими настройками серии
            if (window.saveTemplateData && typeof window.saveTemplateData === 'function') {
                // Преобразуем настройки для формата, который ожидает бэкэнд
                const seriesDataForBackend = {
                    is_series: window.seriesSettings.isSeries,
                    series_max: window.seriesSettings.seriesMax,
                    series_current: window.seriesSettings.seriesCurrent,
                    // Дублируем с другими ключами для совместимости
                    isSeries: window.seriesSettings.isSeries,
                    seriesMax: window.seriesSettings.seriesMax,
                    seriesCurrent: window.seriesSettings.seriesCurrent
                };
                
                console.log('Отправка настроек серии на сервер:', seriesDataForBackend);
                // Проверяем наличие функции saveTemplateData и вызываем ее
                if (typeof window.saveTemplateData === 'function') {
                    window.saveTemplateData(seriesDataForBackend);
                } else if (window.TemplateDataCollector && typeof window.TemplateDataCollector.saveTemplateData === 'function') {
                    window.TemplateDataCollector.saveTemplateData(seriesDataForBackend);
                }
            } else {
                console.error('❌ Функция saveTemplateData не найдена');
                alert('Ошибка сохранения шаблона: функция сохранения недоступна');
                this.disabled = false; // Разблокируем кнопку в случае ошибки
            }
        });
    }
    
    // Обрабатываем клик по кнопке сохранения в мобильной навигации
    const saveMediaBtn = document.getElementById('save-media-btn');
    if (saveMediaBtn) {
        console.log('Инициализация кнопки сохранения');
        
        // Удаляем существующие обработчики событий для избежания дублирования
        const clone = saveMediaBtn.cloneNode(true);
        saveMediaBtn.parentNode.replaceChild(clone, saveMediaBtn);
        
        // Добавляем новый обработчик для клика
        clone.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Клик по кнопке сохранения');
            
            // Проверяем существование модального окна
            if (seriesModal) {
                console.log('Открываем модальное окно настроек серии');
                // Используем существующий экземпляр модального окна или создаем новый
                if (window.seriesModalInstance) {
                    window.seriesModalInstance.show();
                } else {
                    const modal = new bootstrap.Modal(seriesModal);
                    modal.show();
                }
            } else {
                console.error('❌ Модальное окно серии не найдено');
                // Если модальное окно не найдено, сохраняем без настроек серии
                if (window.saveTemplateData && typeof window.saveTemplateData === 'function') {
                    window.saveTemplateData();
                }
            }
        });
        
        // Добавляем обработчик для касания на мобильных устройствах
        clone.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Касание кнопки сохранения на мобильном устройстве');
            
            // Открываем модальное окно
            if (seriesModal) {
                // Используем существующий экземпляр модального окна или создаем новый
                if (window.seriesModalInstance) {
                    window.seriesModalInstance.show();
                } else {
                    const modal = new bootstrap.Modal(seriesModal);
                    modal.show();
                }
            }
        });
    } else {
        console.error('❌ Кнопка сохранения не найдена');
    }
});
