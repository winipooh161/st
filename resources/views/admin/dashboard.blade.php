@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <h2 class="mb-4">Панель администратора</h2>
            
            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i> Добро пожаловать в панель администратора!
            </div>
            
            <h4 class="mb-3">Административные функции</h4>
            <div class="row row-cols-1 row-cols-md-3 g-4 mb-5">
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-people me-2"></i> Пользователи</h5>
                            <p class="card-text">Управление учетными записями пользователей.</p>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">Управление пользователями</a>
                        </div>
                    </div>
                </div>
                
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-file-earmark-text me-2"></i> Шаблоны</h5>
                            <p class="card-text">Управление HTML шаблонами для пользователей.</p>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('admin.templates.index') }}" class="btn btn-primary">Управление шаблонами</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
                      