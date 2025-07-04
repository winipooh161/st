@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
          <div class="text-center mb-4">
                    <div class="avatar-upload-container position-relative mx-auto" style="">
                        <img id="profile-avatar-preview"
                            src="{{ Auth::user()->avatar ? asset('storage/avatars/'.Auth::user()->avatar) : asset('images/default-avatar.jpg') }}"
                            class="profile-avatar  w-100 h-100"
                            alt="Аватар пользователя"
                            style="object-fit: cover;">

                       
                    </div>

                   
                </div>
            <!-- Сообщения об успехе/ошибке -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <div class="card">
               
                <div class="card-body">
                    @if($userTemplates->count() > 0)
                        <div class="instagram-grid">
                            <div class="row">
                                @foreach($userTemplates as $userTemplate)
                                <div class="col-md-4 ">
                                    <div class="reel-card">
                                        <!-- Ссылка на всю карточку -->
                                        <a href="{{ route('user-templates.show', $userTemplate) }}" class="card-link-overlay"></a>
                                        <div class="reel-content">
                                            <!-- Превью шаблона с обложкой -->
                                            @if($userTemplate->cover_path)
                                                @if($userTemplate->cover_type == 'image')
                                                    <img src="{{ Storage::url($userTemplate->cover_path) }}" 
                                                         alt="{{ $userTemplate->name }}" 
                                                         class="img-fluid w-100 h-100 object-fit-cover">
                                                @elseif($userTemplate->cover_type == 'video')
                                                    <video class="w-100 h-100 object-fit-cover" autoplay loop muted playsinline>
                                                        <source src="{{ Storage::url($userTemplate->cover_path) }}" type="video/mp4">
                                                    </video>
                                                    @if($userTemplate->cover_thumbnail)
                                                        <img src="{{ Storage::url($userTemplate->cover_thumbnail) }}" 
                                                             alt="{{ $userTemplate->name }}" 
                                                             class="thumbnail-preview">
                                                    @endif
                                                @endif
                                            @elseif($userTemplate->preview_image)
                                                <img src="{{ asset('storage/' . $userTemplate->preview_image) }}" 
                                                     alt="{{ $userTemplate->name }}" 
                                                     class="img-fluid">
                                            @else
                                                <div class="placeholder-content">
                                                    <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                                    <h6 class="mt-2 text-muted">{{ $userTemplate->name }}</h6>
                                                </div>
                                            @endif
                                            
                                            <!-- Информация о владельце шаблона -->
                                            <div class="template-owner-info">
                                                <span class="badge bg-info">Автор: {{ $userTemplate->user->name }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-file-earmark-plus display-1 text-muted"></i>
                            <h5 class="mt-3">Пока нет доступных шаблонов</h5>
                            <p class="text-muted mb-4">Будьте первым, кто создаст шаблон!</p>
                            @auth
                                <a href="{{ route('templates.create') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-lg me-2"></i>Создать шаблон
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Войти, чтобы создать шаблон
                                </a>
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card-body {
        padding: 0 !important
    }
    .instagram-grid {
        width: 100%;
        height: 100vh;
    }
    
    .reel-card {
        width: 100%;
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    
    .reel-card:before {
        content: "";
        display: block;
        padding-top: 177.8%; /* Соотношение сторон 9:16 для формата Reels */
    }
    
    .reel-card:hover {
        transform: translateY(-5px);
    }
    
    .card-link-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 10;
    }
    
    .reel-content {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #f8f9fa;
        display: flex;
        justify-content: center;
        align-items: center;
        border: 1px solid #e0e0e0;
    }
    
    .placeholder-content {
        font-size: 1.2rem;
        color: #6c757d;
        text-align: center;
    }
    
    /* Стили для информации о владельце */
    .template-owner-info {
        position: absolute;
        bottom: 10px;
        right: 10px;
        z-index: 5;
    }
    
    @media (max-width: 768px) {
        .col-md-4 {
            padding: 0 0px;
        }
        
        .instagram-grid .row {
            margin: 0 0px;
        }
    }
    .col-md-4 {
        flex: 0 0 auto;
        width: 33.33333333%;
        padding: 0;margin: 0;
    }
    
    .object-fit-cover {
        object-fit: cover;
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
    
    video.object-fit-cover {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
    }
</style>
@endsection
