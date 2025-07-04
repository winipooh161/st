@extends('layouts.app')

@section('title', 'Создание шаблона')

@section('styles')
    @include('templates.components.template-styles')
@endsection

@section('content')
    {{-- Основной редактор шаблонов --}}
    @include('templates.components.template-editor-core')
@endsection

@section('scripts')
    {{-- Скрипты для работы с редактором шаблонов --}}
    @include('templates.components.template-scripts')
    

@endsection

