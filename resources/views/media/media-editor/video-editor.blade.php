<!-- Секция для редактирования видео -->
<div id="videoEditorSection" class="video-editor-section" style="display: none;">
    <div class="reset-button-container">
        <button type="button" id="resetBtn" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>
            Назад
        </button>
    </div>
    
    <div class="video-container">
        <video id="videoPreview" class="video-preview" controls>
            Ваш браузер не поддерживает воспроизведение видео.
        </video>
    </div>
    
    <div class="video-timeline-container">
        <div class="timeline-header">
            <h5 class="mb-3">Обрезка видео</h5>
            <p class="text-muted small">Выберите фрагмент до 15 секунд</p>
        </div>
        
        <!-- Мобильный слайдер для обрезки видео -->
        <div class="mobile-trim-slider" id="mobileTrimSlider">
            <div class="mobile-range-track" id="mobileRangeTrack">
                <div class="mobile-range-progress" id="mobileProgressBar"></div>
                <div class="mobile-handle start-handle" id="mobileStartHandle">
                    <div class="handle-grip"></div>
                </div>
                <div class="mobile-handle end-handle" id="mobileEndHandle">
                    <div class="handle-grip"></div>
                </div>
            </div>
        </div>
        
        <div class="timeline-info mt-3">
            <div class="d-flex justify-content-between">
                <span>Начало: <span id="startTime">0.0</span>с</span>
                <span>Конец: <span id="endTime">0.0</span>с</span>
            </div>
            <div class="text-center mt-2">
                <small class="text-muted">Длительность: <span id="selectedDuration">0.0</span>с</small>
            </div>
        </div>
    </div>
</div>
