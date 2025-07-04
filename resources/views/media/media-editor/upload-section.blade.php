<!-- Секция загрузки файла -->
<div id="uploadSection" class="media-upload-section">
    <div class="upload-content text-center">
        <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-4"></i>
        <h3 class="mb-3">Загрузите медиафайл</h3>
        <p class="text-muted mb-4">Поддерживаются изображения (JPG, PNG, GIF) и видео (MP4, MOV, AVI)</p>
        
        <input type="file" id="mediaFile" class="d-none" accept="image/*,video/*">
        <button type="button" id="uploadBtn" class="btn btn-primary btn-lg">
            <i class="fas fa-upload me-2"></i>
            Выбрать файл
        </button>
        
        <div class="mt-4">
            <small class="text-muted">
                Или перетащите файл в эту область
            </small>
        </div>
    </div>
</div>
    