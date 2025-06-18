@extends('adminlte::page')

@section('title', 'Novo Template')

@section('content_header')
    <h1>Cadastrar Template</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.templates.store') }}" method="POST" id="templateForm">
                @include('admin.templates._form', ['submitButtonText' => 'Criar'])
            </form>
        </div>
    </div>
@stop
