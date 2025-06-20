@extends('adminlte::page')

@section('title', 'Editar Configuração')

@section('content_header')
    <h1>Editar Configuração</h1>
@stop

@section('content')
    <form action="{{ route('admin.opportunity-settings.update', $opportunitySetting) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.opportunity-settings._form', ['submitButtonText' => 'Atualizar'])
    </form>
@stop
