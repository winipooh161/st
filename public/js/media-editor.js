// Состояние редактора
const MediaEditor = {
    state: {
        currentFile: null,
        currentFileType: null, // Всегда будет 'image'
        imageScale: 1,
        imageX: 0,
        imageY: 0,
        isDragging: false,
        lastTouch: null
    },

    // Инициализация
    init() {
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

        // Кнопка сохранения из мобильной навигации
        const saveMediaBtn = document.getElementById('save-media-btn');
        if (saveMediaBtn) {
            console.log('✅ Найдена кнопка save-media-btn, добавляем обработчик');
            saveMediaBtn.addEventListener('click', this.handleSave.bind(this));
        } else {
            console.log('ℹ️ Кнопка save-media-btn не найдена (возможно, не мобильная версия)');
        }

        // События для изображения
        const imageViewport = document.getElementById('imageViewport');
        if (imageViewport) {
            this.setupImageEditor(imageViewport);
        }

        // События для видео отключены
    },

    // Настройка drag & drop
    setupDragAndDrop() {
        const container = document.querySelector('.media-editor-container');
        if (!container) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, this.preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            container.addEventListener(eventName, this.highlight.bind(this), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            container.addEventListener(eventName, this.unhighlight.bind(this), false);
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
        this.state.currentFileType = 'image'; // Всегда обрабатываем только изображения
        
        console.log('✅ Файл валиден, тип:', this.state.currentFileType);

        const reader = new FileReader();
        reader.onload = (e) => {
            console.log('📖 Файл прочитан, показываем редактор');
            this.showImageEditor(e.target.result);
        };
        reader.readAsDataURL(file);
    },

    // Валидация файла
    validateFile(file) {
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        const maxSize = 50 * 1024 * 1024; // 50MB

        if (!allowedTypes.includes(file.type)) {
            alert('Неподдерживаемый тип файла. Разрешены только изображения: JPG, PNG, GIF');
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
        this.hideSection('videoEditorSection');
        this.showSection('imageEditorSection');
        this.showSection('actionButtons');

        const imagePreview = document.getElementById('imagePreview');
        if (imagePreview) {
            // Сначала убедимся, что у нас правильные стили
            imagePreview.style.display = 'block';
            imagePreview.style.maxWidth = 'none';
            imagePreview.style.maxHeight = 'none';
            imagePreview.style.position = 'absolute';
            imagePreview.style.opacity = '1';
            
            // Устанавливаем источник изображения
            imagePreview.src = imageURL;
            
            // Обработчик загрузки изображения
            imagePreview.onload = () => {
                // Сначала сбрасываем трансформацию
                this.resetImageTransform();
                
                // Затем вычисляем оптимальное масштабирование для соответствия 
                // размеров изображения размерам viewport
                const viewport = document.getElementById('imageViewport');
                if (viewport) {
                    // Обновляем viewport для формата Reels перед вычислением размеров
                    const viewportRatio = 9/16; // Соотношение сторон Reels (ширина/высота)
                    let maxHeight, maxWidth;
                    
                    if (window.innerWidth > 768) {
                        // На десктопе ограничиваем высоту
                        maxHeight = Math.min(window.innerHeight * 0.8, 800);
                        maxWidth = maxHeight * viewportRatio;
                    } else {
                        // На мобильных - высота 80% экрана
                        maxHeight = window.innerHeight * 0.8;
                        maxWidth = maxHeight * viewportRatio;
                    }
                    
                    // Применяем размеры к viewport
                    viewport.style.height = maxHeight + 'px';
                    viewport.style.width = maxWidth + 'px';
                    
                    // Получаем обновленные размеры viewport
                    const viewportRect = viewport.getBoundingClientRect();
                    const imgWidth = imagePreview.naturalWidth;
                    const imgHeight = imagePreview.naturalHeight;
                    
                    console.log('🖼️ Размеры изображения и viewport:', {
                        imgWidth,
                        imgHeight,
                        viewportWidth: viewportRect.width,
                        viewportHeight: viewportRect.height,
                        viewportRatio
                    });
                    
                    // Сохраняем оригинальные размеры для последующей обработки
                    imagePreview.dataset.originalWidth = imgWidth;
                    imagePreview.dataset.originalHeight = imgHeight;
                    
                    // Вычисляем соотношение сторон
                    const imageRatio = imgWidth / imgHeight;
                    // Используем реальное соотношение сторон viewport
                    const viewportActualRatio = viewportRect.width / viewportRect.height;
                    
                    console.log('📊 Соотношения сторон:', {
                        imageRatio: imageRatio,
                        viewportRatio: viewportRatio,
                        viewportActualRatio: viewportActualRatio,
                        imgWidth: imgWidth,
                        imgHeight: imgHeight,
                        viewportWidth: viewportRect.width,
                        viewportHeight: viewportRect.height
                    });
                    
                    // Задаем начальный масштаб так, чтобы изображение заполняло viewport полностью
                    // Для Reels выбираем размер, который заполнит viewport по меньшему измерению
                    if (imageRatio > viewportRatio) {
                        // Изображение шире, чем viewport 9:16 - масштабируем по высоте
                        this.state.imageScale = viewportRect.height / imgHeight;
                    } else {
                        // Изображение выше, чем viewport 9:16 - масштабируем по ширине
                        this.state.imageScale = viewportRect.width / imgWidth;
                    }
                    
                    console.log('📏 Вычисленный масштаб до корректировки:', this.state.imageScale);
                    
                    // Увеличиваем масштаб немного, чтобы избежать пустых краев
                    // Установка масштаба на 5% больше для лучшего визуального эффекта
                    this.state.imageScale *= 1.05;
                    
                    // Центрируем изображение относительно viewport с повышенной точностью
                    // Учитываем полную ширину и высоту изображения с учетом масштаба
                    const scaledImgWidth = imgWidth * this.state.imageScale;
                    const scaledImgHeight = imgHeight * this.state.imageScale;
                    
                    // Смещение, чтобы изображение было в центре viewport
                    this.state.imageX = (viewportRect.width - scaledImgWidth) / 2;
                    this.state.imageY = (viewportRect.height - scaledImgHeight) / 2;
                    
                    console.log('🎯 Вычисляем центрирование:', {
                        scaledImgWidth,
                        scaledImgHeight,
                        viewportWidth: viewportRect.width,
                        viewportHeight: viewportRect.height,
                        imageX: this.state.imageX,
                        imageY: this.state.imageY
                    });
                    
                    console.log('🎯 Начальная трансформация:', {
                        scale: this.state.imageScale,
                        x: this.state.imageX,
                        y: this.state.imageY
                    });
                    
                    // Применяем трансформацию
                    this.updateImageTransform();
                }
            };
        }
    },

    // Показать редактор видео - заглушка (полностью отключена)
    showVideoEditor(videoURL) {
        alert('В данной версии поддерживается только редактирование изображений');
        // Возвращаемся к форме загрузки
        this.handleReset();
    },

    // Настройка редактора изображений
    setupImageEditor(viewport) {
        let startX, startY, startImageX, startImageY;

        // Мышь
        viewport.addEventListener('mousedown', (e) => {
            this.state.isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startImageX = this.state.imageX;
            startImageY = this.state.imageY;
        });

        document.addEventListener('mousemove', (e) => {
            if (!this.state.isDragging) return;
            
            const deltaX = e.clientX - startX;
            const deltaY = e.clientY - startY;
            
            this.state.imageX = startImageX + deltaX;
            this.state.imageY = startImageY + deltaY;
            
            this.updateImageTransform();
        });

        document.addEventListener('mouseup', () => {
            this.state.isDragging = false;
        });

        // Колесо мыши для масштабирования
        viewport.addEventListener('wheel', (e) => {
            e.preventDefault();
            const delta = e.deltaY > 0 ? 0.9 : 1.1;
            
            // Расчет нового масштаба
            const oldScale = this.state.imageScale;
            this.state.imageScale *= delta;
            this.state.imageScale = Math.max(0.1, Math.min(5, this.state.imageScale));
            
            // Если масштаб изменился, адаптируем положение изображения
            if (oldScale !== this.state.imageScale) {
                const imagePreview = document.getElementById('imagePreview');
                if (imagePreview && viewport) {
                    const viewportRect = viewport.getBoundingClientRect();
                    const scaledWidth = imagePreview.naturalWidth * this.state.imageScale;
                    const scaledHeight = imagePreview.naturalHeight * this.state.imageScale;
                    
                    // Центрируем изображение в viewport
                    if (scaledWidth < viewportRect.width) {
                        this.state.imageX = (viewportRect.width - scaledWidth) / 2;
                    }
                    if (scaledHeight < viewportRect.height) {
                        this.state.imageY = (viewportRect.height - scaledHeight) / 2;
                    }
                }
            }
            
            this.updateImageTransform();
        });

        // Сенсорные события
        viewport.addEventListener('touchstart', (e) => {
            if (e.touches.length === 1) {
                this.state.isDragging = true;
                const touch = e.touches[0];
                startX = touch.clientX;
                startY = touch.clientY;
                startImageX = this.state.imageX;
                startImageY = this.state.imageY;
            }
        });

        viewport.addEventListener('touchmove', (e) => {
            e.preventDefault();
            
            if (e.touches.length === 1 && this.state.isDragging) {
                const touch = e.touches[0];
                const deltaX = touch.clientX - startX;
                const deltaY = touch.clientY - startY;
                
                this.state.imageX = startImageX + deltaX;
                this.state.imageY = startImageY + deltaY;
                
                this.updateImageTransform();
            }
        });

        viewport.addEventListener('touchend', () => {
            this.state.isDragging = false;
        });
    },

    // Сброс трансформации изображения
    resetImageTransform() {
        // Устанавливаем масштаб на 1, но позицию в центр viewport
        const imagePreview = document.getElementById('imagePreview');
        const viewport = document.getElementById('imageViewport');
        
        if (imagePreview && viewport) {
            // Убедимся, что изображение видно
            imagePreview.style.display = 'block';
            imagePreview.style.opacity = '1';
            
            // Установим начальный масштаб
            this.state.imageScale = 1;
            
            // Центрируем изображение
            const viewportRect = viewport.getBoundingClientRect();
            this.state.imageX = (viewportRect.width - imagePreview.naturalWidth) / 2;
            this.state.imageY = (viewportRect.height - imagePreview.naturalHeight) / 2;
            
            console.log('🔄 Сброс трансформации:', {
                imageScale: this.state.imageScale,
                imageX: this.state.imageX,
                imageY: this.state.imageY,
                viewportWidth: viewportRect.width,
                viewportHeight: viewportRect.height,
                imageWidth: imagePreview.naturalWidth,
                imageHeight: imagePreview.naturalHeight
            });
        } else {
            // Если элементы не найдены, используем значения по умолчанию
            this.state.imageScale = 1;
            this.state.imageX = 0;
            this.state.imageY = 0;
        }
        
        this.updateImageTransform();
    },

    // Обновление трансформации изображения - оптимизировано для формата Reels
    updateImageTransform() {
        const imagePreview = document.getElementById('imagePreview');
        const viewport = document.getElementById('imageViewport');
        
        if (imagePreview && viewport) {
            // Фиксированное соотношение сторон для viewport (ширина/высота)
            const viewportRatio = 9/16; // Соотношение сторон Reels
            
            // Определяем максимальные размеры для viewport в зависимости от экрана
            let maxHeight, maxWidth;
            if (window.innerWidth > 768) {
                // На десктопе ограничиваем высоту до 80% от высоты окна
                maxHeight = Math.min(window.innerHeight * 0.8, 800);
                maxWidth = maxHeight * viewportRatio;
            } else {
                // На мобильных - высота 80% экрана, ширина пропорциональная
                maxHeight = window.innerHeight * 0.8;
                maxWidth = maxHeight * viewportRatio;
            }
            
            // Применяем размеры к viewport
            viewport.style.height = maxHeight + 'px';
            viewport.style.width = maxWidth + 'px';
            viewport.style.margin = '0 auto';
            viewport.style.overflow = 'hidden'; // Обрезаем всё, что выходит за границы
            
            // Всегда применяем формат Reels (9:16)
            viewport.classList.add('reels-format');
            
            // Центрируем изображение в viewport по умолчанию
            imagePreview.style.transformOrigin = '0 0';
            
            // Проверяем, чтобы хотя бы часть изображения всегда была видна
            const viewportRect = viewport.getBoundingClientRect();
            const imgWidth = imagePreview.naturalWidth * this.state.imageScale;
            const imgHeight = imagePreview.naturalHeight * this.state.imageScale;
            
            // Проверка и корректировка положения по X и Y, чтобы часть изображения была видна
            if (this.state.imageX > viewportRect.width) {
                this.state.imageX = viewportRect.width - 50; // Оставляем хотя бы 50px изображения видимым
            } else if (this.state.imageX + imgWidth < 0) {
                this.state.imageX = -imgWidth + 50;
            }
            
            if (this.state.imageY > viewportRect.height) {
                this.state.imageY = viewportRect.height - 50;
            } else if (this.state.imageY + imgHeight < 0) {
                this.state.imageY = -imgHeight + 50;
            }
            
            // Применяем трансформацию с учетом начальной точки изображения (верхний левый угол)
            imagePreview.style.transform = `translate(${this.state.imageX.toFixed(2)}px, ${this.state.imageY.toFixed(2)}px) scale(${this.state.imageScale.toFixed(4)})`;
            
            // Исправление: убедимся, что изображение видимо
            imagePreview.style.display = 'block';
            imagePreview.style.opacity = '1';
            
            console.log('🔄 Обновление трансформации:', {
                scale: this.state.imageScale,
                x: this.state.imageX,
                y: this.state.imageY,
                imgWidth,
                imgHeight,
                viewportWidth: viewportRect.width,
                viewportHeight: viewportRect.height
            });
            
            // Обновляем отладочную информацию
            if (document.querySelector('.crop-info')) {
                document.querySelector('.crop-info').classList.remove('d-none');
                document.getElementById('viewport-size').textContent = `${viewport.clientWidth}×${viewport.clientHeight}`;
                document.getElementById('image-size').textContent = `${imagePreview.naturalWidth}×${imagePreview.naturalHeight}`;
                document.getElementById('image-scale').textContent = this.state.imageScale.toFixed(2);
                document.getElementById('image-offset').textContent = `X: ${this.state.imageX.toFixed(0)}px, Y: ${this.state.imageY.toFixed(0)}px`;
            }
            
            console.log('🖼️ Обновление трансформации изображения:', {
                scale: this.state.imageScale,
                x: this.state.imageX,
                y: this.state.imageY,
                viewportWidth: viewport.clientWidth,
                viewportHeight: viewport.clientHeight,
                imageWidth: imagePreview.naturalWidth,
                imageHeight: imagePreview.naturalHeight
            });
            
            // Обновляем стили для mobile-friendly интерфейса
            const isMobile = window.innerWidth <= 991.98;
            if (isMobile) {
                // Если есть кнопки действий, фиксируем их внизу экрана
                const actionButtons = document.getElementById('actionButtons');
                if (actionButtons) {
                    actionButtons.classList.add('mobile-fixed');
                }
            }
            
            // Явно указываем границы viewport для визуального обозначения области кадрирования
            viewport.style.border = '2px dashed #f44336';
            viewport.style.borderRadius = '8px';
            viewport.style.boxSizing = 'border-box';
            viewport.style.position = 'relative';
            
            // Добавляем визуальную подсказку о рамке кадрирования
            const existingHelp = document.getElementById('crop-frame-help');
            if (!existingHelp) {
                const helpText = document.createElement('div');
                helpText.id = 'crop-frame-help';
                helpText.style.position = 'absolute';
                helpText.style.bottom = '10px';
                helpText.style.left = '0';
                helpText.style.right = '0';
                helpText.style.textAlign = 'center';
                helpText.style.backgroundColor = 'rgba(0,0,0,0.5)';
                helpText.style.color = 'white';
                helpText.style.padding = '5px';
                helpText.style.borderRadius = '4px';
                helpText.style.fontSize = '12px';
                helpText.style.zIndex = '999';
                helpText.innerHTML = 'Эта рамка показывает область, которая будет сохранена';
                viewport.appendChild(helpText);
            }
            
            // Логируем текущие размеры и трансформацию для отладки
            console.log('📐 Обновление трансформации:', {
                imageScale: this.state.imageScale,
                imageX: this.state.imageX,
                imageY: this.state.imageY,
                viewportWidth: viewport.clientWidth,
                viewportHeight: viewport.clientHeight,
                naturalWidth: imagePreview.naturalWidth,
                naturalHeight: imagePreview.naturalHeight
            });
        }
    },

    // Заглушки для видео функций
    handleVideoLoaded(e) {
        console.log('Функция обработки видео отключена');
    },

    setupVideoSliders() {
        // Функционал отключен
    },

    updateVideoSliders() {
        // Функционал отключен
    },

    updateTimeDisplay() {
        // Функционал отключен
        if (endTimeEl) endTimeEl.textContent = this.state.videoEnd.toFixed(1);
        if (durationEl) durationEl.textContent = (this.state.videoEnd - this.state.videoStart).toFixed(1);
    },

    // Показ сообщений пользователю
    showMessage(message, type = 'info') {
        // Удаляем предыдущее сообщение, если есть
        const existingMessage = document.querySelector('.media-editor-message');
        if (existingMessage) {
            existingMessage.remove();
        }

        // Создаем новое сообщение
        const messageEl = document.createElement('div');
        messageEl.className = `media-editor-message alert alert-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'}`;
        messageEl.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            animation: slideDown 0.3s ease-out;
        `;
        messageEl.textContent = message;

        // Добавляем CSS анимацию, если её нет
        if (!document.querySelector('#media-editor-styles')) {
            const styles = document.createElement('style');
            styles.id = 'media-editor-styles';
            styles.textContent = `
                @keyframes slideDown {
                    from { transform: translateX(-50%) translateY(-100%); opacity: 0; }
                    to { transform: translateX(-50%) translateY(0); opacity: 1; }
                }
            `;
            document.head.appendChild(styles);
        }

        document.body.appendChild(messageEl);

        // Автоматически убираем сообщение через несколько секунд
        setTimeout(() => {
            if (messageEl && messageEl.parentNode) {
                messageEl.remove();
            }
        }, type === 'error' ? 5000 : 3000);
    },

    // Сохранение
    async handleSave() {
        console.log('🚀 MediaEditor.handleSave: Начало процесса сохранения');
        console.log('📊 Состояние редактора:', {
            currentFile: this.state.currentFile ? {
                name: this.state.currentFile.name,
                type: this.state.currentFile.type,
                size: this.state.currentFile.size
            } : null,
            currentFileType: this.state.currentFileType
        });
        
        if (!this.state.currentFile) {
            console.error('❌ Нет файла для сохранения');
            alert('Сначала выберите файл для обработки');
            return;
        }

        console.log('📁 Сохраняем файл:', {
            name: this.state.currentFile.name,
            type: this.state.currentFileType,
            size: this.state.currentFile.size
        });

        this.showProcessing();
        
        // Изменяем текст кнопки
        const saveBtn = document.getElementById('saveBtn');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Обработка...';
        }

        try {
            const formData = new FormData();
            formData.append('media_file', this.state.currentFile);
            
            console.log('📝 Добавляем данные в FormData...');
            
            // Добавляем данные только для изображений
            {
                // Для изображений добавляем данные кропа
                const imageViewport = document.getElementById('imageViewport');
                const imagePreview = document.getElementById('imagePreview');
                
                // Получаем размеры для правильного расчета соотношения сторон Reels
                const viewportRect = imageViewport ? imageViewport.getBoundingClientRect() : null;
                const imageRect = imagePreview ? imagePreview.getBoundingClientRect() : null;
                
                // Получаем текущую трансформацию изображения относительно холста
                const currentTransform = imagePreview ? window.getComputedStyle(imagePreview).transform : 'none';
                
                // Получаем значения размера холста viewport
                const viewportWidth = viewportRect ? viewportRect.width : 0;
                const viewportHeight = viewportRect ? viewportRect.height : 0;
                
                // Получаем оригинальные размеры изображения
                const origWidth = imagePreview ? imagePreview.naturalWidth : 0;
                const origHeight = imagePreview ? imagePreview.naturalHeight : 0;
                
                // Вычисляем какую часть изображения нужно вырезать, исходя из того что видно в окне просмотра
                
                // Получаем реальные размеры viewport и изображения
                const imgRect = imagePreview.getBoundingClientRect();
                
                // Вычисляем координаты viewport относительно трансформированного изображения
                const viewportToImageCoords = {
                    // Если imageX отрицательный, viewport начинается левее края изображения
                    // Если imageX положительный, viewport начинается правее левого края изображения
                    left: this.state.imageX < 0 ? Math.abs(this.state.imageX) / this.state.imageScale : 0,
                    top: this.state.imageY < 0 ? Math.abs(this.state.imageY) / this.state.imageScale : 0,
                };
                
                // Размеры области в координатах оригинального изображения
                // Делим размер viewport на масштаб, чтобы получить размер области в оригинальном изображении
                const visibleBox = {
                    left: viewportToImageCoords.left,
                    top: viewportToImageCoords.top,
                    width: viewportWidth / this.state.imageScale,
                    height: viewportHeight / this.state.imageScale
                };
                
                // Добавляем дополнительную проверку для безопасности
                if (visibleBox.left + visibleBox.width > origWidth) {
                    console.warn('⚠️ Ширина области выходит за пределы изображения', {
                        visibleBoxRight: visibleBox.left + visibleBox.width,
                        origWidth
                    });
                }
                
                if (visibleBox.top + visibleBox.height > origHeight) {
                    console.warn('⚠️ Высота области выходит за пределы изображения', {
                        visibleBoxBottom: visibleBox.top + visibleBox.height,
                        origHeight
                    });
                }
                
                console.log('🔍 Вычисленный видимый прямоугольник:', {
                    left: visibleBox.left,
                    top: visibleBox.top,
                    width: visibleBox.width,
                    height: visibleBox.height,
                    imageScale: this.state.imageScale,
                    imageX: this.state.imageX,
                    imageY: this.state.imageY,
                    viewportWidth,
                    viewportHeight,
                    origWidth,
                    origHeight,
                    viewportToImageCoords
                });
                
                // Добавляем вспомогательные линии для отладки
                try {
                    // Рисуем рамку вокруг кадрируемой области для отладки
                    let debugElement = document.getElementById('debug-crop-area');
                    if (!debugElement && viewport) {
                        const div = document.createElement('div');
                        div.id = 'debug-crop-area';
                        div.style.position = 'absolute';
                        div.style.border = '2px solid yellow';
                        div.style.pointerEvents = 'none';
                        div.style.zIndex = '1000';
                        viewport.appendChild(div);
                        debugElement = div;
                    }
                    
                    if (debugElement) {
                        const left = this.state.imageX + (viewportToImageCoords.left * this.state.imageScale);
                        const top = this.state.imageY + (viewportToImageCoords.top * this.state.imageScale);
                        const width = visibleBox.width * this.state.imageScale;
                        const height = visibleBox.height * this.state.imageScale;
                        
                        debugElement.style.left = left + 'px';
                        debugElement.style.top = top + 'px';
                        debugElement.style.width = width + 'px';
                        debugElement.style.height = height + 'px';
                    }
                } catch (e) {
                    console.error('Ошибка при создании отладочного элемента', e);
                }
                
                // Ограничиваем область кадрирования границами изображения
                if (visibleBox.left + visibleBox.width > origWidth) {
                    visibleBox.width = origWidth - visibleBox.left;
                }
                if (visibleBox.top + visibleBox.height > origHeight) {
                    visibleBox.height = origHeight - visibleBox.top;
                }
                
                // Убеждаемся, что значения не отрицательные и не выходят за границы
                visibleBox.left = Math.max(0, Math.min(origWidth - 1, visibleBox.left));
                visibleBox.top = Math.max(0, Math.min(origHeight - 1, visibleBox.top));
                visibleBox.width = Math.max(1, Math.min(origWidth - visibleBox.left, visibleBox.width));
                visibleBox.height = Math.max(1, Math.min(origHeight - visibleBox.top, visibleBox.height));
                
                const cropData = {
                    scale: this.state.imageScale,
                    x: this.state.imageX,
                    y: this.state.imageY,
                    // Добавляем информацию о соотношении сторон и формате Reels
                    format: 'reels',
                    aspectRatio: '9:16',
                    viewportWidth: viewportWidth,
                    viewportHeight: viewportHeight,
                    imageWidth: imageRect ? imageRect.width : 0,
                    imageHeight: imageRect ? imageRect.height : 0,
                    originalWidth: origWidth,
                    originalHeight: origHeight,
                    // Данные для точного кадрирования
                    crop: {
                        left: visibleBox.left,
                        top: visibleBox.top,
                        width: visibleBox.width,
                        height: visibleBox.height
                    }
                };
                
                formData.append('crop_data', JSON.stringify(cropData));
                console.log('🖼️ Добавлены данные изображения для формата Reels:', cropData);
            }

            // Добавляем ID шаблона, если он есть
            const templateId = document.getElementById('templateId');
            if (templateId) {
                formData.append('template_id', templateId.value);
                console.log('🏷️ Добавлен ID шаблона:', templateId.value);
            }
            
            // Добавляем URL для возврата, если он указан
            const returnUrl = document.getElementById('returnUrl');
            if (returnUrl) {
                formData.append('return_url', returnUrl.value);
                console.log('🔙 Добавлен URL для возврата:', returnUrl.value);
            }

            // Проверяем наличие CSRF токена
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (!csrfToken) {
                throw new Error('CSRF токен не найден на странице');
            }
            
            console.log('🔐 CSRF токен найден');
            console.log('🌐 Отправляем запрос на /media/process...');

            const response = await fetch('/media/process', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken.getAttribute('content')
                }
            });

            console.log('📡 Получен ответ от сервера:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            console.log('📋 Результат обработки:', result);

            if (result.success) {
                console.log('✅ Файл успешно сохранён!');
                
                // Показываем сообщение об успехе
                this.showMessage('Файл успешно обработан! Переходим к созданию шаблона...', 'success');
                
                // Обновляем кнопку
                if (saveBtn) {
                    saveBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Успешно!';
                    saveBtn.classList.remove('btn-primary');
                    saveBtn.classList.add('btn-success');
                }
                
                // Проверяем наличие URL для перенаправления 
                // (сначала return_url из ответа сервера, затем returnUrl из скрытого поля)
                let redirectUrl = result.return_url || (document.getElementById('returnUrl')?.value);
                
                // Если URL всё еще не найден, используем шаблон по умолчанию
                if (!redirectUrl) {
                    redirectUrl = '/templates/create';
                    console.log('🔄 URL для перенаправления не найден, используем URL по умолчанию:', redirectUrl);
                } else {
                    console.log('🔄 Перенаправляем на:', redirectUrl);
                }
                
                // Небольшая задержка для показа успешного статуса
                setTimeout(() => {
                    console.log('🚀 Выполняем перенаправление на:', redirectUrl);
                    window.location.href = redirectUrl;
                }, 1000);
            } else {
                throw new Error(result.message || 'Ошибка при сохранении файла');
            }
        } catch (error) {
            console.error('❌ Ошибка при сохранении:', error);
            this.showMessage('Произошла ошибка: ' + error.message, 'error');
            
            // Восстанавливаем кнопку
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Далее';
                saveBtn.classList.remove('btn-success');
                saveBtn.classList.add('btn-primary');
            }
        } finally {
            this.hideProcessing();
            console.log('🏁 MediaEditor.handleSave: Процесс завершен');
        }
    },

    // Сброс
    handleReset() {
        this.state.currentFile = null;
        this.state.currentFileType = null;
        
        this.hideSection('imageEditorSection');
        this.hideSection('videoEditorSection');
        this.hideSection('actionButtons');
        this.showSection('uploadSection');

        // Очистка input
        const mediaFile = document.getElementById('mediaFile');
        if (mediaFile) mediaFile.value = '';
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
            indicator.style.display = 'block';
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
    console.log('🚀 Инициализация MediaEditor');
    MediaEditor.init();
    
    // Убеждаемся, что объект доступен глобально
    window.MediaEditor = MediaEditor;
    
    // Переопределяем функцию processMedia для лучшей совместимости
    window.processMedia = function() {
        console.log('🔧 processMedia: Функция вызвана');
        
        if (window.MediaEditor && typeof window.MediaEditor.handleSave === 'function') {
            console.log('✅ processMedia: MediaEditor найден, вызываем handleSave');
            // Используем bind для сохранения контекста
            return window.MediaEditor.handleSave.call(window.MediaEditor);
        } else {
            console.error('❌ processMedia: MediaEditor не инициализирован или handleSave недоступен');
            console.log('Доступные объекты:', {
                MediaEditor: !!window.MediaEditor,
                handleSave: window.MediaEditor ? typeof window.MediaEditor.handleSave : 'MediaEditor отсутствует'
            });
            // В случае ошибки пробуем перенаправить на базовый шаблон
            setTimeout(() => {
                window.location.href = '/template-editor/edit/1';
            }, 300);
            return false;
        }
    };
    
    console.log('✅ MediaEditor успешно инициализирован и доступен глобально');
});

// Экспорт для использования в других скриптах
window.MediaEditor = MediaEditor;
