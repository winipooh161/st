@extends('layouts.app')


{{-- Подключаем наши стили --}}
<link rel="stylesheet" href="/css/templates/date-picker-enhanced.css">
<link rel="stylesheet" href="/css/templates/simple-calendar.css">

{{-- Подключаем наши скрипты --}}


@section('styles')
<style>
    .template-viewer-container {
        min-height: 100vh;
        background: #f8f9fa  ;      position: relative;
        overflow-x: hidden;
    }
    
    .cover-section {
        height: 75vh;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        position: relative;
   
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 2px solid #e0e0e0;
    }

    .cover-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        color: #6c757d;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
    }

    .template-actions {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 10;
    }
    
    .template-content-wrapper {
        width: 100%;
        min-height: 800px;
        height: auto;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 30px;
        margin-bottom: 40px;
    }
    
    .object-fit-cover {
        object-fit: cover;
        width: 100%;
        height: 100%;
    }
    
    video.object-fit-cover {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
    }
    
    .thumbnail-preview {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: -1;
        opacity: 0.5;
    }
    
    /* Стили для блока информации о серии */
    .series-info-block {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        margin: 20px 0;
        /* Обеспечиваем видимость блока для серий */
        display: block;
    }
    
    .series-info-block .progress {
        height: 8px;
        background-color: #e9ecef;
        border-radius: 4px;
        margin: 10px 0;
        overflow: hidden;
    }
    
    .series-info-block .progress-bar {
        background-color: #28a745;
        height: 100%;
        transition: width 0.3s ease;
    }
    
    .series-info-block .series-stats {
        display: flex;
        justify-content: space-between;
        font-size: 0.9em;
        color: #6c757d;
    }

    .series-badge {
        display: inline-block;
        background-color: #198754;
        color: white;
        font-size: 0.8rem;
        padding: 0.2rem 0.5rem;
        border-radius: 0.25rem;
        margin-bottom: 10px;
    }
    
    /* Стили для QR-кода блока */
    .qr-code-section {
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
    }
    
    .qr-code-container {
        background: white;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 20px;
        text-align: center;
    }
    
    .qr-code-container img, .qr-code-img {
        max-width: 100%;
        height: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        margin: 10px 0;
    }
    
    .qr-code-container h6 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 10px;
    }
    
    .qr-code-container .small {
        font-size: 0.85rem;
    }
    
    .qr-code-container .btn {
        transition: all 0.2s ease;
    }
    
    .qr-code-container .btn:hover {
        transform: translateY(-1px);
    }
    
    .qr-loading {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 2px solid #f3f3f3;
        border-top: 2px solid #007bff;
        border-radius: 50%;
        animation: qr-spin 1s linear infinite;
    }
    
    @keyframes qr-spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class=" template-viewer-container">
  
    <!-- Кнопки действий -->
    <div class="template-actions">
        @auth
            @if($userTemplate->user_id != Auth::id())
                @php
                    // Проверяем, сохранял ли пользователь этот конкретный шаблон
                    $hasSaved = Auth::user()->userTemplates()
                                ->where('name', 'like', '%' . $userTemplate->name . ' (копия)%')
                                ->exists();
                @endphp
                @if(!$hasSaved)
                    <form action="{{ route('user-templates.save', $userTemplate->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Сохранить в коллекцию
                        </button>
                    </form>
                @else
                    <button class="btn btn-secondary" disabled>
                        <i class="fas fa-check me-1"></i> В вашей коллекции
                    </button>
                @endif
            @else
                <!-- Если это шаблон текущего пользователя, показываем соответствующую кнопку -->
                <span class="badge bg-info">Это ваше</span>
            @endif
        @else
            <a href="{{ route('login') }}" class="btn btn-outline-primary">
                <i class="fas fa-sign-in-alt me-1"></i> Войдите, чтобы сохранить
            </a>
        @endauth
    </div>
    
    <!-- Секция обложки -->
    <div class="cover-section">
        <div id="coverContainer" class="cover-placeholder">
            @if($userTemplate->cover_path)
                @if($userTemplate->cover_type == 'image')
                    <img src="{{ Storage::url($userTemplate->cover_path) }}" alt="{{ $userTemplate->name }}" class="object-fit-cover">
                @elseif($userTemplate->cover_type == 'video')
                    <video class="object-fit-cover" autoplay loop muted playsinline>
                        <source src="{{ Storage::url($userTemplate->cover_path) }}" type="video/mp4">
                    </video>
                    @if($userTemplate->cover_thumbnail)
                        <img src="{{ Storage::url($userTemplate->cover_thumbnail) }}" alt="{{ $userTemplate->name }}" class="thumbnail-preview">
                    @endif
                @endif
            @elseif($userTemplate->preview_image)
                <img src="{{ asset('storage/' . $userTemplate->preview_image) }}" alt="{{ $userTemplate->name }}" class="object-fit-cover">
            @else
                <div class="text-center">
                    <div class="mb-3">
                        <i class="fas fa-image fa-4x text-muted"></i>
                    </div>
                    <h4 class="text-muted">Обложка отсутствует</h4>
                </div>
            @endif
        </div>
  
    </div>

    @php
        // Проверяем, является ли шаблон серией
        $isSeries = $userTemplate->is_series && $userTemplate->series_max > 0;
        // Логируем для отладки на сервере
        \Illuminate\Support\Facades\Log::info('Series template check: ', [
            'id' => $userTemplate->id,
            'is_series' => $userTemplate->is_series,
            'series_max' => $userTemplate->series_max,
            'series_current' => $userTemplate->series_current,
            'isSeries' => $isSeries
        ]);
    @endphp
    
    <!-- Блок информации о серии -->
    @if($isSeries)
        <div id="seriesInfoBlock" class="series-info-block" style="display:block;">
            <span class="series-badge">Серия</span>
            <div class="progress">
                <div class="progress-bar" role="progressbar" 
                     style="width: {{ ($userTemplate->series_max > 0) ? (($userTemplate->series_max - $userTemplate->series_current) / $userTemplate->series_max) * 100 : 0 }}%;" 
                     aria-valuenow="{{ $userTemplate->series_max - $userTemplate->series_current }}" 
                     aria-valuemin="0" 
                     aria-valuemax="{{ $userTemplate->series_max }}">
                </div>
            </div>
            <div class="series-stats">
                <span>Использовано: <span class="used-count">{{ $userTemplate->series_max - $userTemplate->series_current }}</span> из <span class="total-steps">{{ $userTemplate->series_max }}</span></span>
                <span class="progress-percentage">{{ ($userTemplate->series_max > 0) ? round((($userTemplate->series_max - $userTemplate->series_current) / $userTemplate->series_max) * 100) : 0 }}%</span>
            </div>
            
            @auth
                @if($userTemplate->user_id == Auth::id())
                    @php
                        // Проверяем, нужно ли показывать QR-код
                        $isOriginalSeries = $userTemplate->is_series && $userTemplate->series_max > 0;
                        $isFromSeries = str_contains($userTemplate->name, '(Получено по QR)');
                        $isUsed = str_contains($userTemplate->name, '(Использован)');
                        $showQrCode = ($isOriginalSeries || $isFromSeries) && !$isUsed;
                    @endphp
                    
                    @if($showQrCode)
                        <!-- QR-код блок -->
                        <div class="qr-code-section mt-3">
                            <div id="qrCodeContainer" class="qr-code-container">
                                <div class="mb-2">
                                    @if($isOriginalSeries && $userTemplate->series_current > 0)
                                        <h6>QR-код для клиента</h6>
                                        <p class="small text-muted">Отсканировав этот QR-код, клиент получит копию шаблона</p>
                                    @elseif($isOriginalSeries && $userTemplate->series_current <= 0)
                                        <h6 class="text-danger">Серия исчерпана</h6>
                                        <p class="small text-muted">Все шаблоны из серии уже использованы</p>
                                    @elseif($isFromSeries)
                                        <h6>QR-код для создателя</h6>
                                        <p class="small text-muted">Создатель шаблона должен отсканировать этот QR-код, чтобы отметить шаблон как использованный</p>
                                    @endif
                                </div>
                                <div id="qrCodeDisplay" class="text-center">
                                    <div id="qrCodeImage"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endauth
        </div>
    @endif
      
    <!-- Содержимое шаблона -->
   
        <div id="template-content" class="template-content-wrapper">
            {!! $html_content !!}
        </div>
    
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateContent = document.getElementById('template-content');
    
    // Диагностический вывод для проверки параметров серии
    console.log('Template series data:', {
        is_series: {{ $userTemplate->is_series ? 'true' : 'false' }},
        series_max: {{ $userTemplate->series_max ?: 0 }},
        series_current: {{ $userTemplate->series_current ?: 0 }}
    });
    
    // Инициализируем обработчик событий шаблона
    initializeTemplateHandlers();
    
    // Настраиваем данные о серии, если нужно
    setupSeriesData();
    
    // Инициализируем QR-код функциональность
    // Сначала проверяем статус серии, а затем инициализируем QR-код
    checkSeriesStatus().then(() => {
        initializeQrCodeHandlers();
    });
    
    /**
     * Инициализирует обработчики событий для шаблона
     */
    function initializeTemplateHandlers() {
        // Найдем все интерактивные элементы шаблона, если они есть
        const interactiveElements = templateContent.querySelectorAll('[data-action]');
        
        // Добавляем обработчики событий для интерактивных элементов
        interactiveElements.forEach(element => {
            element.addEventListener('click', function(e) {
                const action = this.getAttribute('data-action');
                
                if (action === 'requestTemplateCopy') {
                    handleTemplateCopyRequest();
                }
            });
        });
    }
    
    /**
     * Инициализирует обработчики для QR-кодов
     */
    function initializeQrCodeHandlers() {
        @auth
            @if($userTemplate->user_id == Auth::id())
                @php
                    // Проверяем, является ли это оригинальным шаблоном серии
                    $isOriginalSeries = $userTemplate->is_series && $userTemplate->series_max > 0;
                    
                    // Проверяем, является ли это копией из серии
                    $isFromSeries = str_contains($userTemplate->name, '(Получено по QR)');
                    
                    // Проверяем, использован ли шаблон
                    $isUsed = str_contains($userTemplate->name, '(Использован)');
                @endphp
                
                @if(($isOriginalSeries || $isFromSeries) && !$isUsed)
                    // Для серии шаблона дополнительно проверяем, что есть доступные использования
                    @if(!$isOriginalSeries || ($isOriginalSeries && $userTemplate->series_current > 0))
                        // Автоматически генерируем и показываем QR-код
                        autoGenerateQrCode({{ $isOriginalSeries ? 'true' : 'false' }});
                    @else
                        console.log('Серия исчерпана - QR-код не генерируется');
                    @endif
                @endif
            @endif
        @endauth
    }
    
    /**
     * Автоматически генерирует и отображает QR-код
     * @param {boolean} isOriginalSeries - true если это оригинальный шаблон серии, false если копия
     */
    async function autoGenerateQrCode(isOriginalSeries = null) {
        // Проверяем доступность шаблонов в серии
        if (isOriginalSeries && {{ $userTemplate->series_current <= 0 ? 'true' : 'false' }}) {
            document.getElementById('qrCodeImage').innerHTML = `
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Серия исчерпана. Все шаблоны использованы.
                </div>
            `;
            return;
        }
        
        // Проверяем, есть ли уже активный QR-код
        try {
            const statusResponse = await fetch('/template-qr/{{ $userTemplate->id }}/status', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const statusData = await statusResponse.json();
            
            // Если есть активный QR для создателя, показываем его
            if (statusData.creator_qr_active && statusData.creator_qr_url) {
                showQrCode(statusData.creator_qr_url);
                return;
            }
            
            // Если есть активный QR для клиента, показываем его
            if (statusData.client_qr_active && statusData.client_qr_url) {
                showQrCode(statusData.client_qr_url);
                return;
            }
            
            // Определяем тип QR-кода для генерации
            let shouldGenerateClientQr = true;
            
            if (isOriginalSeries !== null) {
                // Используем переданный параметр
                shouldGenerateClientQr = isOriginalSeries;
            } else {
                // Определяем автоматически
                const isOriginalCreator = checkIfOriginalCreator();
                const isFromSeries = checkIfFromSeries();
                shouldGenerateClientQr = isOriginalCreator && isFromSeries;
            }
            
            if (shouldGenerateClientQr) {
                // Генерируем QR для клиентов (оригинальный создатель серии)
                await generateAndShowClientQr();
            } else {
                // Генерируем QR для деактивации (получатель шаблона из серии)
                await generateAndShowCreatorQr();
            }
            
        } catch (error) {
            console.error('Ошибка при автогенерации QR-кода:', error);
        }
    }
    
    /**
     * Проверяет, является ли пользователь оригинальным создателем серии
     */
    function checkIfOriginalCreator() {
        // Проверяем, является ли это оригинальной серией
        const isOriginalSeries = {{ $userTemplate->is_series ? 'true' : 'false' }} && {{ $userTemplate->series_max ?: 0 }} > 0;
        return isOriginalSeries;
    }
    
    /**
     * Проверяет, является ли шаблон частью серии (включая полученные копии)
     */
    function checkIfFromSeries() {
        // Проверяем, является ли это оригинальной серией
        const isOriginalSeries = {{ $userTemplate->is_series ? 'true' : 'false' }} && {{ $userTemplate->series_max ?: 0 }} > 0;
        
        // Проверяем, является ли это копией из серии (по названию)
        const templateName = '{{ $userTemplate->name }}';
        const isSeriesCopy = templateName.includes('(Получено по QR)');
        
        return isOriginalSeries || isSeriesCopy;
    }
    
    /**
     * Генерирует и показывает QR-код для клиента
     */
    async function generateAndShowClientQr() {
        try {
            const response = await fetch('/template-qr/{{ $userTemplate->id }}/generate-client', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showQrCode(data.qr_url);
                // Устанавливаем интервал для проверки использования QR-кода
                startQrStatusMonitoring();
            } else {
                console.error('Ошибка при создании QR-кода:', data.error);
            }
        } catch (error) {
            console.error('Ошибка при генерации QR-кода:', error);
        }
    }
    
    /**
     * Отображает QR-код
     */
    function showQrCode(qrUrl) {
        const qrContainer = document.getElementById('qrCodeImage');
        if (qrContainer) {
            qrContainer.innerHTML = `<img src="${qrUrl}" alt="QR-код" class="qr-code-img">`;
        }
    }
    
    /**
     * Запускает мониторинг статуса QR-кода
     */
    function startQrStatusMonitoring() {
        // Проверяем статус каждые 5 секунд
        const interval = setInterval(async () => {
            try {
                const response = await fetch('/template-qr/{{ $userTemplate->id }}/status', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const data = await response.json();
                
                // Если клиент отсканировал QR и получил шаблон, генерируем QR для создателя
                if (!data.client_qr_active && data.series_current < {{ $userTemplate->series_current }}) {
                    clearInterval(interval);
                    await generateAndShowCreatorQr();
                }
                
            } catch (error) {
                console.error('Ошибка при мониторинге QR-кода:', error);
            }
        }, 5000);
        
        // Останавливаем мониторинг через 30 минут
        setTimeout(() => {
            clearInterval(interval);
        }, 1800000);
    }
    
    /**
     * Генерирует и показывает QR-код для создателя (деактивация)
     */
    async function generateAndShowCreatorQr() {
        try {
            const response = await fetch('/template-qr/{{ $userTemplate->id }}/generate-creator', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showQrCode(data.qr_url);
            } else {
                console.error('Ошибка при создании QR-кода для создателя:', data.error);
            }
        } catch (error) {
            console.error('Ошибка при генерации QR-кода для создателя:', error);
        }
    }
    
    /**
     * Генерирует QR-код для клиента
     */
    async function generateClientQrCode() {
        const btn = document.getElementById('generateClientQr');
        const originalText = btn.innerHTML;
        
        try {
            btn.innerHTML = '<span class="qr-loading"></span> Генерация...';
            btn.disabled = true;
            
            const response = await fetch('/template-qr/{{ $userTemplate->id }}/generate-client', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showClientQr(data.qr_url);
                showNotification('QR-код для клиента создан!', 'success');
            } else {
                showNotification(data.error || 'Ошибка при создании QR-кода', 'error');
            }
        } catch (error) {
            console.error('Ошибка:', error);
            showNotification('Произошла ошибка при создании QR-кода', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
    
    /**
     * Генерирует QR-код для создателя
     */
    async function generateCreatorQrCode() {
        const btn = document.getElementById('generateCreatorQr');
        const originalText = btn.innerHTML;
        
        try {
            btn.innerHTML = '<span class="qr-loading"></span> Генерация...';
            btn.disabled = true;
            
            const response = await fetch('/template-qr/{{ $userTemplate->id }}/generate-creator', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                showCreatorQr(data.qr_url);
                showNotification('QR-код для деактивации создан!', 'success');
            } else {
                showNotification(data.error || 'Ошибка при создании QR-кода', 'error');
            }
        } catch (error) {
            console.error('Ошибка:', error);
            showNotification('Произошла ошибка при создании QR-кода', 'error');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }
    
    /**
     * Проверяет статус QR-кодов
     */
    async function checkQrStatus() {
        try {
            const response = await fetch('/template-qr/{{ $userTemplate->id }}/status', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            if (data.client_qr_active && data.client_qr_url) {
                showClientQr(data.client_qr_url);
            }
            
            if (data.creator_qr_active && data.creator_qr_url) {
                showCreatorQr(data.creator_qr_url);
            }
        } catch (error) {
            console.error('Ошибка при проверке статуса QR-кодов:', error);
        }
    }
    
    /**
     * Показывает QR-код для клиента
     */
    function showClientQr(qrUrl) {
        document.getElementById('noQrMessage').style.display = 'none';
        document.getElementById('clientQrDisplay').style.display = 'block';
        document.getElementById('clientQrImage').innerHTML = `<img src="${qrUrl}" alt="QR-код для клиента">`;
    }
    
    /**
     * Показывает QR-код для создателя
     */
    function showCreatorQr(qrUrl) {
        document.getElementById('clientQrDisplay').style.display = 'none';
        document.getElementById('creatorQrDisplay').style.display = 'block';
        document.getElementById('creatorQrImage').innerHTML = `<img src="${qrUrl}" alt="QR-код для деактивации">`;
    }
    
    /**
     * Сбрасывает QR-коды
     */
    function resetQrCodes() {
        document.getElementById('noQrMessage').style.display = 'block';
        document.getElementById('clientQrDisplay').style.display = 'none';
        document.getElementById('creatorQrDisplay').style.display = 'none';
        document.getElementById('clientQrImage').innerHTML = '';
        document.getElementById('creatorQrImage').innerHTML = '';
    }
    
    /**
     * Показывает уведомление
     */
    function showNotification(message, type = 'info') {
        // Создаем элемент уведомления
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Автоматически удаляем через 5 секунд
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
    
    /**
     * Обрабатывает запрос на получение копии шаблона
     */
    function handleTemplateCopyRequest() {
        @auth
            // Проверяем, есть ли у пользователя уже копия из этой серии
            const userHasCopy = {{ isset($userHasCopy) && $userHasCopy ? 'true' : 'false' }};
            
            if (userHasCopy) {
                alert('У вас уже есть копия из этой серии шаблонов.');
            } else {
                // Отправляем форму для сохранения шаблона
                document.querySelector('form[action="{{ route('user-templates.save', $userTemplate->id) }}"]')?.submit();
            }
        @else
            // Перенаправляем на страницу входа
            window.location.href = '{{ route('login') }}';
        @endauth
    }
    
    /**
     * Настраивает данные о серии, если они доступны
     */
    function setupSeriesData() {
        // Обновляем информацию в блоке серии, если она доступна
        const seriesBlock = document.getElementById('seriesInfoBlock') || document.querySelector('.series-info-block');
        
        // Проверяем условия для серии и делаем блок видимым, если это серия
        if (seriesBlock && {{ $userTemplate->is_series ? 'true' : 'false' }} && {{ $userTemplate->series_max ?: 0 }} > 0) {
            // Явно делаем блок видимым
            seriesBlock.style.display = 'block';
            
            const progressBar = seriesBlock.querySelector('.progress-bar');
            const currentStep = seriesBlock.querySelector('.current-step');
            const totalSteps = seriesBlock.querySelector('.total-steps');
            const progressPercentage = seriesBlock.querySelector('.progress-percentage');
            
            const seriesMax = {{ $userTemplate->series_max ?: 0 }};
            const seriesCurrent = {{ $userTemplate->series_current ?: 0 }};
            
            if (progressBar) {
                const usedCount = seriesMax - seriesCurrent;
                const percentage = (seriesMax > 0) ? (usedCount / seriesMax) * 100 : 0;
                progressBar.style.width = percentage + '%';
                progressBar.setAttribute('aria-valuenow', usedCount);
                progressBar.setAttribute('aria-valuemax', seriesMax);
            }
            
            const usedCountElement = seriesBlock.querySelector('.used-count');
            if (usedCountElement) usedCountElement.textContent = seriesMax - seriesCurrent;
            if (totalSteps) totalSteps.textContent = seriesMax;
            if (progressPercentage) progressPercentage.textContent = Math.round(
                (seriesMax > 0) ? ((seriesMax - seriesCurrent) / seriesMax) * 100 : 0
            ) + '%';
            
            // Логирование для отладки
            console.log('Series data updated:', {
                isVisible: seriesBlock.style.display !== 'none',
                max: seriesMax,
                current: seriesCurrent,
                percentage: Math.round((seriesMax > 0) ? (seriesCurrent / seriesMax) * 100 : 0) + '%'
            });
        } else {
            // Если это не серия, скрываем блок
            if (seriesBlock) {
                seriesBlock.style.display = 'none';
                console.log('Series block hidden (not a series or invalid data)');
            } else {
                console.log('Series block not found in the DOM');
            }
        }
    }
    
    /**
     * Проверяет статус серии шаблонов
     */
    async function checkSeriesStatus() {
        try {
            const response = await fetch('/template-qr/{{ $userTemplate->id }}/series-status', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const data = await response.json();
            
            // Обновляем интерфейс на основе статуса серии
            if (!data.can_generate_qr) {
                // Если нельзя генерировать QR-код, показываем соответствующее сообщение
                if (data.is_series && !data.series_available) {
                    document.getElementById('qrCodeImage').innerHTML = `
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Серия исчерпана. Все шаблоны использованы.
                        </div>
                    `;
                } else if (data.is_used) {
                    document.getElementById('qrCodeImage').innerHTML = `
                        <div class="alert alert-info">
                            <i class="fas fa-check-circle me-2"></i>
                            Шаблон уже использован.
                        </div>
                    `;
                }
            }
            
            return data;
        } catch (error) {
            console.error('Ошибка при проверке статуса серии:', error);
            return {
                can_generate_qr: false,
                error: true
            };
        }
    }
});
</script>
@endsection
