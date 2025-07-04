<!-- Дополнительные стили для медиа-редактора -->
<style>
.fullscreen-editor {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: 1000;
    background: white;
}

.media-upload-section {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
    background: #f8f9fa;
}

.upload-content {
    max-width: 400px;
    padding: 2rem;
}

.image-editor-section,
.video-editor-section {
    height: 100vh;
    position: relative;
    overflow: hidden;
}

.image-viewport {
    width: 100%;
    height: 100vh;
    overflow: hidden;
    position: relative;
    background: #000;
    cursor: grab;
    touch-action: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-viewport:active {
    cursor: grabbing;
}

.image-preview {
    position: absolute;
    max-width: none;
    max-height: none;
    user-select: none;
    pointer-events: none;
    object-fit: contain;
}

.video-container {
    height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    background: #000;
}

.video-preview {
    max-width: 100%;
    max-height: 100%;
}

.video-timeline-container {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 1rem;
    border-top: 1px solid #dee2e6;
}

/* Стили для мобильного слайдера */
.mobile-trim-slider {
    position: relative;
    height: 60px;
    margin: 1rem 0;
}

.mobile-range-track {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    transform: translateY(-50%);
}

.mobile-range-progress {
    position: absolute;
    top: 0;
    height: 100%;
    background: #007bff;
    border-radius: 4px;
    transition: all 0.1s ease;
}

.mobile-handle {
    position: absolute;
    top: 50%;
    width: 24px;
    height: 24px;
    background: #007bff;
    border: 2px solid white;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    cursor: pointer;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.mobile-handle:active {
    transform: translate(-50%, -50%) scale(1.2);
}

.handle-grip {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    transform: translate(-50%, -50%);
}

.gesture-hint {
    position: absolute;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.gesture-hint i {
    margin-right: 0.5rem;
}

/* Адаптивность */
@media (max-width: 768px) {
    .reset-button-container {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 20;
    }
    
    .video-timeline-container {
        padding: 0.75rem;
    }
    
    .timeline-header h5 {
        font-size: 1rem;
    }
}
</style>
