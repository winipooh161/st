<!-- Кнопки действий -->
<div id="actionButtons" class="action-buttons" style="display: none;">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" id="saveBtn" class="btn btn-success btn-lg">
                        <i class="fas fa-save me-2"></i>
                        Сохранить
                    </button>
                    <button type="button" id="cancelBtn" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-times me-2"></i>
                        Отмена
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.action-buttons {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    padding: 1rem;
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    z-index: 1000;
}

@media (max-width: 768px) {
    .action-buttons .btn {
        min-width: 120px;
    }
}
</style>
