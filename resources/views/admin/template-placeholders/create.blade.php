@extends('adminlte::page')

@section('title', 'Novo Placeholder')

@section('content_header')
    <h1>Cadastrar Placeholder</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.template-placeholders.store') }}" method="POST">
                @include('admin.template-placeholders._form', ['submitButtonText' => 'Criar'])
            </form>
        </div>
    </div>
@stop
