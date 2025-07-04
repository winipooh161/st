
<div class="modal fade" id="seriesModal" tabindex="-1" aria-labelledby="seriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="seriesModalLabel">Настройки</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>
            <div class="modal-body">
      
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="isSeriesCheckbox">
                    <label class="form-check-label" for="isSeriesCheckbox">
                        <strong>Создать серию </strong>
                    </label>
                </div>
                
                <div id="seriesSettingsContainer" style="display: none;">
                    <div class="mb-3">
                        <label for="seriesMaxInput" class="form-label">Общее количество  в серии:</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="seriesMaxInput" min="1" value="10">
                            <span class="input-group-text">шт.</span>
                        </div>
                     </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-primary" id="saveSeriesSettings" >Сохранить</button>
            </div>
        </div>
    </div>
</div>
<style>
    .modal-dialog-centered {
        display: flex;
        align-items: flex-end;
        min-height: calc(90% - var(--bs-modal-margin) * 2);
    }
</style>

<script>
    // Расширенная инициализация модального окна
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🔍 Инициализация модального окна серии шаблонов');
        const seriesModal = document.getElementById('seriesModal');
        const isSeriesCheckbox = document.getElementById('isSeriesCheckbox');
        const seriesMaxInput = document.getElementById('seriesMaxInput');
        const seriesSettingsContainer = document.getElementById('seriesSettingsContainer');
        
        // Проверяем наличие необходимых элементов
        if (!seriesModal || !isSeriesCheckbox || !seriesMaxInput || !seriesSettingsContainer) {
            console.error('❌ Не все элементы модального окна найдены');
            return;
        }
        
        // Инициализация модального окна
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            // Удаляем старый экземпляр модального окна, если он существует
            if (window.seriesModalInstance) {
                try {
                    window.seriesModalInstance.dispose();
                } catch (e) {
                    console.warn('Не удалось удалить старый экземпляр модального окна:', e);
                }
            }
            
            // Создаем и сохраняем экземпляр модального окна в глобальной переменной
            window.seriesModalInstance = new bootstrap.Modal(seriesModal);
            console.log('✅ Экземпляр модального окна создан успешно');
            
            // При открытии модального окна обновляем состояние из глобальных настроек
            seriesModal.addEventListener('show.bs.modal', function() {
                console.log('📂 Открытие модального окна серии');
                
                // Получаем текущие настройки
                const currentSettings = window.seriesSettings || {
                    isSeries: false,
                    seriesMax: 10,
                    seriesCurrent: 0,
                    is_series: false,
                    series_max: 10,
                    series_current: 0
                };
                
                // Обновляем интерфейс
                isSeriesCheckbox.checked = currentSettings.isSeries;
                seriesMaxInput.value = currentSettings.seriesMax;
                seriesSettingsContainer.style.display = currentSettings.isSeries ? 'block' : 'none';
                
                console.log('📊 Загружены настройки серии:', currentSettings);
            });
        }
    });
</script>
<?php /**PATH C:\OSPanel\domains\tyty\resources\views/templates/components/series-modal.blade.php ENDPATH**/ ?>