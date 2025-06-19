@extends('adminlte::page')

@section('content_header')
  <h1>Gerar Termos de um Edital</h1>
@stop

@section('content')
  <form action="{{ route('admin.terms.store') }}" method="POST">
    @csrf

    <div class="form-group">
      <label for="opportunity_id">Edital</label>
      <select name="opportunity_id" id="opportunity_id" class="form-control" required>
        <option value="">-- selecione --</option>
        @foreach($opportunities as $op)
          <option value="{{ $op->id }}">{{ $op->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group">
      <label for="template_id">Template</label>
      <select name="template_id" id="template_id" class="form-control" required>
        <option value="">-- selecione --</option>
        @foreach($templates as $tpl)
          <option value="{{ $tpl->id }}">{{ $tpl->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="form-group mb-3">
        <label for="start_number">Número Inicial</label>
        <input type="number" name="start_number" id="start_number"
                class="form-control"
                value="{{ old('start_number', 1) }}"
                min="1">
        <small class="form-text text-muted">
            Se o template usar @{{id}}, será substituído por Número/2025, incrementando em cada termo.
        </small>
    </div>

    <button type="submit" class="btn btn-success">
      Gerar Termos
    </button>
  </form>
@stop
