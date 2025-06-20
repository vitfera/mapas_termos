@extends('adminlte::page')

@section('title', 'Editar Mapeamento')

@section('content_header')
    <h1>Editar Mapeamento #{{ $placeholderMapping->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.placeholder-mappings.update', $placeholderMapping) }}" method="POST" id="templateForm">
                @method('PUT')
                @include('admin.placeholder-mappings._form', ['submitButtonText' => 'Atualizar'])
            </form>
        </div>
    </div>
@stop
