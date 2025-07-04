@extends('adminlte::page')

@section('title', 'Mapeamentos')

@section('content_header')
    <h1>Mapeamentos de Placeholders</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('admin.placeholder-mappings.create') }}"
       class="btn btn-primary mb-3">Novo Mapeamento</a>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Key</th>
                        <th>Label</th>
                        <th>Oportunidade</th>
                        <th>Campo</th>
                        <th>Prioridade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mappings as $map)
                        <tr>
                            <td>{{ $map->id }}</td>
                            <td>{{ $map->placeholder_key }}</td>
                            <td>{{ $map->placeholder_label }}</td>
                            <td>{{ $map->opportunity->name ?? $map->opportunity_id }}</td>
                            <td>{{ $map->field->title ?? $map->field_id }}</td>
                            <td>{{ $map->priority }}</td>
                            <td>
                                <a href="{{ route('admin.placeholder-mappings.show', $map) }}"
                                   class="btn btn-sm btn-info">Ver</a>
                                <a href="{{ route('admin.placeholder-mappings.edit', $map) }}"
                                   class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.placeholder-mappings.destroy', $map) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Confirma exclusão?')">
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
        {{ $mappings->links('pagination::bootstrap-4') }}
    </div>
@stop
