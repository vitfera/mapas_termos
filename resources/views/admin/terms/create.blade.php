@extends('adminlte::page')

@section('content_header')
  <h1>Gerar Termos de um Edital</h1>
@stop

@section('content')
  <form id="termsForm" action="{{ route('admin.terms.store') }}" method="POST">
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

    <button id="btnGenerate" type="submit" class="btn btn-success">
      <span id="btnText">Gerar Termos</span>
      <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2" role="status" aria-hidden="true" style="display: none;"></span>
    </button>
  </form>
@stop

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const form     = document.getElementById('termsForm');
  const btn      = document.getElementById('btnGenerate');
  const btnText  = document.getElementById('btnText');
  const spinner  = document.getElementById('btnSpinner');

  form.addEventListener('submit', function() {
    // Desativa o botão e mostra spinner
    btn.disabled = true;
    btnText.textContent = 'Gerando...';
    spinner.style.display = 'inline-block';

    // Quando a janela voltar a ter foco (download finalizado), resetamos
    function resetButton() {
      btn.disabled = false;
      btnText.textContent = 'Gerar Termos';
      spinner.style.display = 'none';
      window.removeEventListener('focus', resetButton);
    }

    window.addEventListener('focus', resetButton);
  });
});
</script>
@endpush
