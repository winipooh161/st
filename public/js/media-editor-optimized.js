/**
 * Оптимизированный медиа-редактор для Laravel приложения
 * Поддерживает загрузку, редактирование и обработку изображений и видео
 */

// Состояние редактора
const MediaEditor = {
    state: {
        currentFile: null,
        currentFileType: null,
        videoDuration: 0,
        videoStart: 0,
        videoEnd: 0,
        imageScale: 1,
        imageX: 0,
        imageY: 0,
        isDragging: false,
        lastTouch: null
    },

    // Инициализация
    init() {
        console.log('🚀 Инициализация MediaEditor (оптимизированная версия)');
        this.bindEvents();
        this.setupDragAndDrop();
    },

    // Привязка событий
    bindEvents() {
        // Кнопка загрузки
        const uploadBtn = document.getElementById('uploadBtn');
        const mediaFile = document.getElementById('mediaFile');
        
        if (uploadBtn && mediaFile) {
            uploadBtn.addEventListener('click', () => mediaFile.click());
            mediaFile.addEventListener('change', this.handleFileSelect.bind(this));
        }

        // Кнопки действий
        const saveBtn = document.getElementById('saveBtn');
        const resetBtn = document.getElementById('resetBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        if (saveBtn) saveBtn.addEventListener('click', this.handleSave.bind(this));
        if (resetBtn) resetBtn.addEventListener('click', this.handleReset.bind(this));
        if (cancelBtn) cancelBtn.addEventListener('click', this.handleReset.bind(this));

        // События для изображения
        const imageViewport = document.getElementById('imageViewport');
        if (imageViewport) {
            this.setupImageEditor(imageViewport);
        }

        // События для видео
        const videoPreview = document.getElementById('videoPreview');
        if (videoPreview) {
            videoPreview.addEventListener('loadedmetadata', this.handleVideoLoaded.bind(this));
        }

        // Мобильные слайдеры
        this.setupVideoSliders();
    },

    // Настройка drag & drop
    setupDragAndDrop() {
        const container = document.querySelector('.media-editor-container');
        if (!container) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            container.addEventListener(eventName, this.highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, this.unhighlight, false);
        });

        container.addEventListener('drop', this.handleDrop.bind(this), false);
    },

    // Предотвращение стандартного поведения
    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    },

    // Подсветка при drag
    highlight(e) {
        e.currentTarget.classList.add('highlight');
    },

    // Снятие подсветки
    unhighlight(e) {
        e.currentTarget.classList.remove('highlight');
    },

    // Обработка drop
    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            this.processFile(files[0]);
        }
    },

    // Обработка выбора файла
    handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.processFile(file);
        }
    },

    // Обработка файла
    processFile(file) {
        console.log('📁 Обработка файла:', {
            name: file.name,
            type: file.type,
            size: file.size
        });
        
        if (!this.validateFile(file)) return;

        this.state.currentFile = file;
        this.state.currentFileType = file.type.startsWith('video/') ? 'video' : 'image';
        
        console.log('✅ Файл валиден, тип:', this.state.currentFileType);

        const reader = new FileReader();
        reader.onload = (e) => {
            if (this.state.currentFileType === 'image') {
                this.showImageEditor(e.target.result);
            } else {
                this.showVideoEditor(e.target.result);
            }
        };
        reader.readAsDataURL(file);
    },

    // Валидация файла
    validateFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/mov', 'video/avi'];
        const maxSize = 50 * 1024 * 1024; // 50MB

        if (!allowedTypes.includes(file.type)) {
            alert('Неподдерживаемый тип файла. Разрешены: ' + allowedTypes.join(', '));
            return false;
        }

        if (file.size > maxSize) {
            alert('Файл слишком большой. Максимальный размер: 50MB');
            return false;
        }

        return true;
    },

    // Показать редактор изображений
    showImageEditor(imageURL) {
        this.hideSection('uploadSection');
        this.showSection('imageEditorSection');
        
        const image = document.getElementById('imagePreview');
        if (image) {
            image.src = imageURL;
            this.resetImageTransform();
        }
    },

    // Показать редактор видео
    showVideoEditor(videoURL) {
        this.hideSection('uploadSection');
        this.showSection('videoEditorSection');
        
        const video = document.getElementById('videoPreview');
        if (video) {
            video.src = videoURL;
        }
    },

    // Настройка редактора изображений
    setupImageEditor(viewport) {
        const image = viewport.querySelector('#imagePreview');
        if (!image) return;

        let startX, startY, initialScale = 1, initialTranslateX = 0, initialTranslateY = 0;

        // Мышь
        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            this.state.imageScale = Math.max(0.5, Math.min(3, this.state.imageScale * delta));
            this.updateImageTransform();
        });

        viewport.addEventListener('mousedown', (e) => {
            this.state.isDragging = true;
            startX = e.clientX - this.state.imageX;
            startY = e.clientY - this.state.imageY;
        });

        document.addEventListener('mousemove', (e) => {
            if (!this.state.isDragging) return;
            this.state.imageX = e.clientX - startX;
            this.state.imageY = e.clientY - startY;
            this.updateImageTransform();
        });

        document.addEventListener('mouseup', () => {
            this.state.isDragging = false;
        });

        // Сенсорные события
        viewport.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                this.state.isDragging = true;
                const touch = e.touches[0];
                startX = touch.clientX - this.state.imageX;
                startY = touch.clientY - this.state.imageY;
            } else if (e.touches.length === 2) {
                this.state.isDragging = false;
                this.state.lastTouch = {
                    distance: this.getTouchDistance(e.touches),
                    scale: this.state.imageScale
                };
            }
        });

        viewport.addEventListener('touchmove', (e) => {
            e.preventDefault();
            
            if (e.touches.length === 1 && this.state.isDragging) {
                const touch = e.touches[0];
                this.state.imageX = touch.clientX - startX;
                this.state.imageY = touch.clientY - startY;
                this.updateImageTransform();
            } else if (e.touches.length === 2 && this.state.lastTouch) {
                const distance = this.getTouchDistance(e.touches);
                const scaleChange = distance / this.state.lastTouch.distance;
                this.state.imageScale = Math.max(0.5, Math.min(3, this.state.lastTouch.scale * scaleChange));
                this.updateImageTransform();
            }
        });

        viewport.addEventListener('touchend', () => {
            this.state.isDragging = false;
            this.state.lastTouch = null;
        });
    },

    // Получение расстояния между двумя касаниями
    getTouchDistance(touches) {
        const dx = touches[0].clientX - touches[1].clientX;
        const dy = touches[0].clientY - touches[1].clientY;
        return Math.sqrt(dx * dx + dy * dy);
    },

    // Сброс трансформации изображения
    resetImageTransform() {
        this.state.imageScale = 1;
        this.state.imageX = 0;
        this.state.imageY = 0;
        this.updateImageTransform();
    },

    // Обновление трансформации изображения
    updateImageTransform() {
        const image = document.getElementById('imagePreview');
        if (image) {
            image.style.transform = `translate(${this.state.imageX}px, ${this.state.imageY}px) scale(${this.state.imageScale})`;
        }
    },

    // Обработка загрузки видео
    handleVideoLoaded(e) {
        this.state.videoDuration = e.target.duration;
        this.state.videoEnd = Math.min(15, this.state.videoDuration);
        this.updateVideoSliders();
        this.updateTimeDisplay();
    },

    // Настройка слайдеров для видео
    setupVideoSliders() {
        const startSlider = document.getElementById('videoStartSlider');
        const endSlider = document.getElementById('videoEndSlider');

        if (startSlider) {
            startSlider.addEventListener('input', (e) => {
                this.state.videoStart = parseFloat(e.target.value);
                if (this.state.videoStart >= this.state.videoEnd) {
                    this.state.videoStart = Math.max(0, this.state.videoEnd - 0.1);
                    e.target.value = this.state.videoStart;
                }
                this.updateTimeDisplay();
            });
        }

        if (endSlider) {
            endSlider.addEventListener('input', (e) => {
                this.state.videoEnd = parseFloat(e.target.value);
                if (this.state.videoEnd <= this.state.videoStart) {
                    this.state.videoEnd = Math.min(this.state.videoDuration, this.state.videoStart + 0.1);
                    e.target.value = this.state.videoEnd;
                }
                this.updateTimeDisplay();
            });
        }
    },

    // Обновление слайдеров
    updateVideoSliders() {
        const startSlider = document.getElementById('videoStartSlider');
        const endSlider = document.getElementById('videoEndSlider');

        if (startSlider) {
            startSlider.max = this.state.videoDuration;
            startSlider.value = this.state.videoStart;
        }

        if (endSlider) {
            endSlider.max = this.state.videoDuration;
            endSlider.value = this.state.videoEnd;
        }
    },

    // Обновление отображения времени
    updateTimeDisplay() {
        const startDisplay = document.getElementById('startTimeDisplay');
        const endDisplay = document.getElementById('endTimeDisplay');

        if (startDisplay) {
            startDisplay.textContent = this.formatTime(this.state.videoStart);
        }

        if (endDisplay) {
            endDisplay.textContent = this.formatTime(this.state.videoEnd);
        }
    },

    // Форматирование времени
    formatTime(seconds) {
        const minutes = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${minutes}:${secs.toString().padStart(2, '0')}`;
    },

    // Сохранение
    async handleSave() {
        console.log('💾 Начинаем сохранение медиафайла');
        
        if (!this.state.currentFile) {
            alert('Файл не выбран');
            return;
        }

        this.showProcessing();

        try {
            const formData = new FormData();
            formData.append('media_file', this.state.currentFile);

            // Добавляем ID шаблона, если есть
            const templateId = this.getTemplateId();
            if (templateId) {
                formData.append('template_id', templateId);
            }

            // Добавляем данные в зависимости от типа файла
            if (this.state.currentFileType === 'image') {
                const cropData = {
                    scale: this.state.imageScale,
                    x: this.state.imageX,
                    y: this.state.imageY
                };
                formData.append('crop_data', JSON.stringify(cropData));
            } else if (this.state.currentFileType === 'video') {
                formData.append('video_start', this.state.videoStart);
                formData.append('video_end', this.state.videoEnd);
            }

            // Получаем CSRF токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF токен не найден');
            }

            const response = await fetch('/media/process', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                }
            });

            const result = await response.json();

            if (result.success) {
                console.log('✅ Файл успешно обработан:', result);
                
                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else {
                    alert('Файл успешно обработан!');
                    // Перенаправляем на создание пользовательского шаблона
                    window.location.href = '/create-template';
                }
            } else {
                throw new Error(result.message || 'Ошибка обработки файла');
            }

        } catch (error) {
            console.error('❌ Ошибка при сохранении:', error);
            alert('Ошибка при сохранении: ' + error.message);
        } finally {
            this.hideProcessing();
        }
    },

    // Получение ID шаблона из URL
    getTemplateId() {
        const urlParts = window.location.pathname.split('/');
        const editorIndex = urlParts.indexOf('editor');
        
        if (editorIndex !== -1 && urlParts[editorIndex + 1]) {
            return urlParts[editorIndex + 1];
        }
        
        return null;
    },

    // Сброс
    handleReset() {
        this.state.currentFile = null;
        this.state.currentFileType = null;
        this.state.videoDuration = 0;
        this.state.videoStart = 0;
        this.state.videoEnd = 0;
        this.resetImageTransform();
        
        this.hideSection('imageEditorSection');
        this.hideSection('videoEditorSection');
        this.showSection('uploadSection');
        
        const mediaFile = document.getElementById('mediaFile');
        if (mediaFile) {
            mediaFile.value = '';
        }
    },

    // Показать секцию
    showSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = 'block';
        }
    },

    // Скрыть секцию
    hideSection(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.style.display = 'none';
        }
    },

    // Показать индикатор загрузки
    showProcessing() {
        const indicator = document.getElementById('processingIndicator');
        if (indicator) {
            indicator.style.display = 'flex';
        }
    },

    // Скрыть индикатор загрузки
    hideProcessing() {
        const indicator = document.getElementById('processingIndicator');
        if (indicator) {
            indicator.style.display = 'none';
        }
    }
};

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 Инициализация MediaEditor (оптимизированная версия)');
    MediaEditor.init();
    
    // Убеждаемся, что объект доступен глобально
    window.MediaEditor = MediaEditor;
    
    // Переопределяем функцию processMedia для лучшей совместимости
    window.processMedia = function() {
        console.log('🔄 Вызов processMedia() через совместимость');
        if (MediaEditor.state.currentFile) {
            return MediaEditor.handleSave();
        } else {
            console.warn('⚠️ processMedia вызван, но файл не выбран');
            alert('Пожалуйста, выберите файл для обработки');
        }
    };
    
    console.log('✅ MediaEditor успешно инициализирован и доступен глобально');
});

// Экспорт для использования в других скриптах
window.MediaEditor = MediaEditor;
