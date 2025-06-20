@extends('adminlte::page')

@section('title', 'Configurações de Edital')

@section('content_header')
    <h1>Configurações de Edital</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('admin.opportunity-settings.sync') }}" method="POST" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-secondary">Sincronizar Editais</button>
    </form>

    <div class="card">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Edital</th>
                        <th>Categoria</th>
                        <th>Start #</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($settings as $s)
                        <tr>
                            <td>{{ $s->opportunity_id }}</td>
                            <td>{{ $s->opportunity->name }}</td>
                            <td>{{ ucfirst($s->category) }}</td>
                            <td>{{ $s->start_number }}</td>
                            <td>
                                <a href="{{ route('admin.opportunity-settings.edit', $s) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('admin.opportunity-settings.destroy', $s) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirma?')">
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
        {{ $settings->links('pagination::bootstrap-4') }}
    </div>
@stop