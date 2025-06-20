@extends('adminlte::page')

@section('title', 'Detalhes do Template')

@section('content_header')
    <h1>Template: {{ $template->name }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $template->id }}</p>
            <p><strong>Categoria:</strong> {{ ucfirst($template->category) }}</p>
            <p><strong>Descrição:</strong><br>{!! nl2br(e($template->description)) !!}</p>
            <p><strong>Cabeçalho (HTML):</strong><br><pre>{{ $template->header_html }}</pre></p>
            <p><strong>Rodapé (HTML):</strong><br><pre>{{ $template->footer_html }}</pre></p>
            <p><strong>Corpo (HTML):</strong><br><pre>{{ $template->body_html }}</pre></p>
            <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">Voltar</a>
            <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-warning">Editar</a>
        </div>
    </div>
@stop
