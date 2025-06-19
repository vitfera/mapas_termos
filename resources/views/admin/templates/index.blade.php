@extends('adminlte::page')

@section('title', 'Templates')

@section('content_header')
    <h1>Templates</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.templates.create') }}" class="btn btn-primary mb-3">Novo Template</a>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($templates as $template)
                        <tr>
                            <td>{{ $template->id }}</td>
                            <td>{{ $template->name }}</td>
                            <td>{{ $template->created_at->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('admin.templates.show', $template) }}" class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $templates->links() }}
    </div>
@stop
