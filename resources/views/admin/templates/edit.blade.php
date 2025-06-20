@extends('adminlte::page')

@section('title', 'Editar Template')

@section('content_header')
    <h1>Editar Template #{{ $template->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.templates.update', $template) }}" method="POST" id="templateForm">
                @method('PUT')
                @include('admin.templates._form', ['submitButtonText' => 'Atualizar'])
            </form>
        </div>
    </div>
@stop
