<!-- Индикатор загрузки -->
<div id="processingIndicator" class="processing-indicator" style="display: none;">
    <div class="processing-overlay">
        <div class="processing-content">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-3 mb-0">Обработка файла...</p>
            <small class="text-muted">Пожалуйста, подождите</small>
        </div>
    </div>
</div>

<style>
.processing-indicator {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
}

.processing-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: flex;
    justify-content: center;
    align-items: center;
}

.processing-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.processing-content .spinner-border {
    width: 3rem;
    height: 3rem;
}
</style>
