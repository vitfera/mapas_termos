@extends('adminlte::page')

@section('title', 'Detalhes do Placeholder')

@section('content_header')
    <h1>Placeholder: {{ $templatePlaceholder->key }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $templatePlaceholder->id }}</p>
            <p><strong>Template:</strong> {{ $templatePlaceholder->template->name }}</p>
            <p><strong>Key:</strong> {{ $templatePlaceholder->key }}</p>
            <p><strong>Label:</strong> {{ $templatePlaceholder->label }}</p>
            <a href="{{ route('admin.template-placeholders.index') }}" class="btn btn-secondary">Voltar</a>
            <a href="{{ route('admin.template-placeholders.edit', $templatePlaceholder) }}" class="btn btn-warning">Editar</a>
        </div>
    </div>
@stop
