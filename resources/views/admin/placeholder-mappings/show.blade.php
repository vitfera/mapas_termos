@extends('adminlte::page')

@section('title', 'Detalhes do Mapeamento')

@section('content_header')
    <h1>Mapeamento #{{ $placeholderMapping->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Placeholder:</strong> {{ $placeholderMapping->placeholder->key }}</p>
            <p><strong>Oportunidade (ID):</strong> {{ $placeholderMapping->opportunity_id }}</p>
            <p><strong>Tipo de Fonte:</strong> {{ ucfirst($placeholderMapping->source_type) }}</p>
            <p><strong>Source Key:</strong> {{ $placeholderMapping->source_key }}</p>
            <p><strong>Prioridade:</strong> {{ $placeholderMapping->priority }}</p>

            <a href="{{ route('admin.placeholder-mappings.index') }}" class="btn btn-secondary">Voltar</a>
            <a href="{{ route('admin.placeholder-mappings.edit', $placeholderMapping) }}" class="btn btn-warning">Editar</a>
        </div>
    </div>
@stop
