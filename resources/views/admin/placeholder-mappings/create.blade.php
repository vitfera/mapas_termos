@extends('adminlte::page')

@section('title', 'Novo Mapeamento')

@section('content_header')
    <h1>Cadastrar Mapeamento</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.placeholder-mappings.store') }}" method="POST" id="templateForm">
                @include('admin.placeholder-mappings._form', ['submitButtonText' => 'Criar'])
            </form>
        </div>
    </div>
@stop
