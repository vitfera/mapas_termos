@extends('adminlte::page')

@section('title', 'Placeholders')

@section('content_header')
    <h1>Placeholders</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.template-placeholders.create') }}" class="btn btn-primary mb-3">Novo Placeholder</a>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Template</th>
                        <th>Key</th>
                        <th>Label</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($placeholders as $ph)
                        <tr>
                            <td>{{ $ph->id }}</td>
                            <td>{{ $ph->template->name }}</td>
                            <td>{{ $ph->key }}</td>
                            <td>{{ $ph->label }}</td>
                            <td>
                                <a href="{{ route('admin.template-placeholders.show', $ph) }}" class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('admin.template-placeholders.edit', $ph) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.template-placeholders.destroy', $ph) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma exclusão?')">
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
        {{ $placeholders->links() }}
    </div>
@stop
