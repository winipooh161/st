{{-- Компонент для отображения обложки шаблона --}}
<div class="cover-section">
    <div id="coverContainer" class="cover-placeholder">
        @if(session('media_editor_file'))
            @if(session('media_editor_type') == 'image')
                <img src="{{ Storage::url(session('media_editor_file')) }}" alt="Обложка" class="img-fluid">
            @elseif(session('media_editor_type') == 'video')
                <video class="w-100" autoplay loop muted playsinline>
                    <source src="{{ Storage::url(session('media_editor_file')) }}" type="video/mp4">
                </video>
                @if(session('media_editor_thumbnail'))
                    <input type="hidden" id="coverThumbnail" value="{{ session('media_editor_thumbnail') }}">
                @endif
            @endif
            <input type="hidden" id="coverPath" value="{{ session('media_editor_file') }}">
            <input type="hidden" id="coverType" value="{{ session('media_editor_type') }}">
        @else
            <div class="cover-icon">
                <i class="fas fa-image"></i>
            </div>
            <div class="cover-text">Место для обложки</div>
            <div class="cover-subtext">Нажмите, чтобы выбрать обложку</div>
            {{-- Добавляем пустые скрытые поля для случаев, когда обложка не загружена --}}
            <input type="hidden" id="coverPath" value="">
            <input type="hidden" id="coverType" value="">
            <input type="hidden" id="coverThumbnail" value="">
        @endif
    </div>
</div>
