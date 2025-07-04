@extends('layouts.app')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/instagram-grid.css') }}">
@endsection

@section('content')
<div class="container py-4">
 
    <!-- Отладочная информация -->
  
<div class="">
       <div class="text-center overflow-hidden position-relative" style="">
        <div class="blur-gradient-effect">
            <img src="{{ isset($profileUser) ? ($profileUser->avatar ? asset('storage/avatars/'.$profileUser->avatar) : asset('images/default-avatar.jpg')) : (Auth::user()->avatar ? asset('storage/avatars/'.Auth::user()->avatar) : asset('images/default-avatar.jpg')) }}"
                class="profile-avatar" alt="Аватар">
                
        </div>
        <div class="abs_title_img">
         <h4 class="mt-3 user-name-display">{{ isset($profileUser) ? $profileUser->name : Auth::user()->name }}</h4>
         <p class="text-muted">{{ isset($profileUser) ? $profileUser->email : Auth::user()->email }}</p>
        </div>
       </div>
        @if (session('status'))
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif
       
    </div>

   

    <div class="content">
        <!-- Instagram-подобная сетка с шаблонами -->
        <div class="instagram-grid">
            @if($templates->count() > 0)
                @foreach($templates as $template)
                    <div class="instagram-grid-item">
                        <div class="instagram-card">
                            <div class="card-header">
                                <div class="card-avatar">
                                    <!-- Аватар шаблона -->
                                </div>
                                <div class="card-username">{{ $template->name }}</div>
                            </div>
                            
                            <div class="card-image">
                                @if($template->cover_path)
                                    @if($template->cover_type === 'video')
                                        <video controls>
                                            <source src="{{ asset('storage/' . $template->cover_path) }}" type="video/mp4">
                                            Ваш браузер не поддерживает видео.
                                        </video>
                                    @else
                                        <img src="{{ asset('storage/' . $template->cover_path) }}" alt="{{ $template->name }}">
                                    @endif
                                @else
                                    <div class="placeholder-image">
                                        <i class="bi bi-file-earmark-text"></i>
                                    </div>
                                @endif
                                
                                <!-- Индикатор VIP-пользователя -->
                                @if($template->target_user_id)
                                    <div class="template-vip-indicator">
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-person-circle"></i> VIP
                                        </span>
                                    </div>
                                @endif
                            </div>
                            
                            <div class="card-actions with-delete">
                                @if(!isset($isOwner) || $isOwner === true)
                                    <button class="btn-action delete-template" data-id="{{ $template->id }}" data-name="{{ $template->name }}" data-bs-toggle="modal" data-bs-target="#deleteTemplateModal">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
                
                <!-- Если шаблонов меньше 8, добавляем пустые ячейки -->
                @for($i = $templates->count(); $i < 15; $i++)
                    <div class="instagram-grid-item">
                        <div class="instagram-card empty-card">
                            <div class="placeholder-image">
                                
                            </div>
                        </div>
                    </div>
                @endfor
            @else
                <!-- Если шаблонов нет, показываем 8 пустых ячеек -->
                @for($i = 0; $i < 15; $i++)
                    <div class="instagram-grid-item">
                        <div class="instagram-card empty-card">
                            <div class="placeholder-image">
                               
                            </div>
                        </div>
                    </div>
                @endfor
            @endif
        </div>
    </div>
</div>


@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-template');
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const templateId = this.getAttribute('data-id');
                    const templateName = this.getAttribute('data-name');
                    
                    document.getElementById('template-name-to-delete').textContent = templateName;
                    document.getElementById('delete-template-form').action = `/client/my-templates/${templateId}`;
                });
            });
        });
    </script>
@endsection


