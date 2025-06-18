@extends('adminlte::page')

@section('title', 'Editar Placeholder')

@section('content_header')
    <h1>Editar Placeholder #{{ $templatePlaceholder->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.template-placeholders.update', $templatePlaceholder) }}" method="POST">
                @method('PUT')
                @include('admin.template-placeholders._form', ['submitButtonText' => 'Atualizar'])
            </form>
        </div>
    </div>
@stop
