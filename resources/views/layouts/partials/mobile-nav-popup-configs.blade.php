<!-- Конфигурации для всплывающих меню мобильной навигации -->
<div id="mobile-nav-popup-configs" class="d-none">
    <!-- Конфигурация для Home -->
    <div data-popup-config="home" class="popup-config">
        <div class="popup-header">Главная</div>
        <div class="popup-items">
            <div class="popup-item" data-icon="newspaper.svg" data-href="/" data-title="Главная страница"></div>
            <div class="popup-item" data-icon="calendar.svg" data-href="/events" data-title="События"></div>
            <div class="popup-item" data-icon="info-circle.svg" data-href="/about" data-title="О нас"></div>
        </div>
    </div>

    <!-- Конфигурация для Profile -->
    <div data-popup-config="profile" class="popup-config">
       
        <div class="popup-items">
            <div class="popup-item" data-icon="speedometer.svg" data-href="#" data-modal="true" data-modal-target="user-profile-modal" data-title="Настройки профиля"></div>
            <div class="popup-item" data-icon="sup.svg" data-href="#" data-modal="true" data-modal-target="sub-profile-modal" data-title="Донат"></div>
            <div class="popup-item" data-icon="share.svg" data-href="#" data-modal="true" data-modal-target="share-profile-modal" data-title="Поделиться"></div>
        </div>
    </div>

    <!-- Конфигурация для My Templates -->
    <div data-popup-config="my-templates" class="popup-config">
        <div class="popup-header">Мой профиль</div>
        <div class="popup-items">
            <div class="popup-item" data-icon="person.svg" data-href="#" data-modal="true" data-modal-target="user-profile-modal" data-title="Настройки профиля"></div>
            <div class="popup-item" data-icon="cash.svg" data-href="#" data-modal="true" data-modal-target="sub-profile-modal" data-title="Донат"></div>
            <div class="popup-item" data-icon="share-fill.svg" data-href="#" data-modal="true" data-modal-target="share-profile-modal" data-title="Поделиться"></div>
        </div>
    </div>

    <!-- Конфигурация для Create -->
    <div data-popup-config="create" class="popup-config">
       
        <div class="popup-items">
            <div class="popup-item" data-icon="back.svg" data-href="javascript:history.back();" data-title=""></div>
        </div>
    </div>

    <!-- Конфигурация для QR-Scanner -->
    <div data-popup-config="qr-scanner" class="popup-config">
        <div class="popup-header">QR-Сканер</div>
        <div class="popup-items">
            <div class="popup-item" data-icon="qr-code.svg" data-href="#" data-modal="true" data-modal-target="qrScannerModal" data-title="Сканировать QR"></div>
            <div class="popup-item" data-icon="camera.svg" data-href="#" data-modal="true" data-modal-target="camera-modal" data-title="Камера"></div>
            <div class="popup-item" data-icon="clock-history.svg" data-href="/qr/history" data-title="История сканирования"></div>
        </div>
    </div>

    <!-- Конфигурация для Games -->
    <div data-popup-config="games" class="popup-config">
    
        <div class="popup-items">
            <div class="popup-item" data-icon="puzzle.svg" data-href="/games/puzzle" data-title=""></div>
            <div class="popup-item" data-icon="controller.svg" data-href="/games/arcade" data-title=""></div>
            <div class="popup-item" data-icon="trophy.svg" data-href="/games/tournaments" data-title=""></div>
        </div>
    </div>

    <!-- Вы можете добавить любые дополнительные конфигурации здесь -->
</div>
