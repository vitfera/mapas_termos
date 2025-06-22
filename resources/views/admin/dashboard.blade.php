@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Painel Administrativo</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <x-adminlte-small-box title="{{ \App\Models\Template::count() }}" text="Templates" icon="fas fa-file-alt" theme="info" url="{{ route('admin.templates.index') }}"/>
        </div>
        <div class="col-md-3">
            <x-adminlte-small-box title="{{ \App\Models\PlaceholderMapping::count() }}" text="Mapeamentos" icon="fas fa-link" theme="warning" url="{{ route('admin.placeholder-mappings.index') }}"/>
        </div>
        <div class="col-md-3">
            <x-adminlte-small-box title="{{ \App\Models\OpportunitySetting::count() }}" text="Configurações" icon="fas fa-cog" theme="success" url="{{ route('admin.opportunity-settings.index') }}"/>
        </div>
        <div class="col-md-3">
            <x-adminlte-small-box title="Gerar" text="Termos" icon="fas fa-file-pdf" theme="primary" url="{{ route('admin.terms.create') }}"/>
        </div>
    </div>
    <p>Bem-vindo ao painel!</p>
@stop
