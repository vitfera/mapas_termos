@extends('adminlte::page')

@section('title', 'Detalhes do Mapeamento')

@section('content_header')
    <h1>Mapeamento #{{ $placeholderMapping->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>Placeholder Key:</strong> {{ $placeholderMapping->placeholder_key }}</p>
            <p><strong>Placeholder Label:</strong> {{ $placeholderMapping->placeholder_label }}</p>
            <p><strong>Oportunidade:</strong>
                {{ optional($placeholderMapping->opportunity)->name ?? $placeholderMapping->opportunity_id }}
            </p>
            <p><strong>Campo do Edital:</strong>
                {{ optional($placeholderMapping->field)->title ?? $placeholderMapping->field_id }}
            </p>
            <p><strong>Prioridade:</strong> {{ $placeholderMapping->priority }}</p>

            <a href="{{ route('admin.placeholder-mappings.index') }}" class="btn btn-secondary">Voltar</a>
            <a href="{{ route('admin.placeholder-mappings.edit', $placeholderMapping) }}" class="btn btn-warning">Editar</a>
        </div>
    </div>
@stop
