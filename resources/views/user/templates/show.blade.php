@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('user.templates') }}">Мои шаблоны</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $template->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Информация о шаблоне</h5>
                </div>
                <div class="card-body">
                    <h5>{{ $template->name }}</h5>
                    <p class="text-muted">Создан: {{ $template->created_at->format('d.m.Y') }}</p>
                    
                    @if($template->cover_path)
                        <div class="mt-3 mb-3">
                            @if($template->cover_type === 'video')
                                <video class="w-100" controls>
                                    <source src="{{ asset($template->cover_path) }}" type="video/mp4">
                                    Ваш браузер не поддерживает видео.
                                </video>
                            @else
                                <img src="{{ asset($template->cover_path) }}" class="img-fluid" alt="{{ $template->name }}">
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Предварительный просмотр</h5>
                </div>
                <div class="card-body">
                    <div class="template-preview border p-3">
                        {!! $template->html_content !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
