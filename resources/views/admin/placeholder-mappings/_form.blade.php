@csrf

<div class="form-group mb-3">
    <label for="placeholder_key">Placeholder Key</label>
    <input type="text"
           name="placeholder_key"
           id="placeholder_key"
           class="form-control"
           value="{{ old('placeholder_key', $placeholderMapping->placeholder_key ?? '') }}"
           placeholder="ex: cpf_cnpj"
           required>
</div>

<div class="form-group mb-3">
    <label for="placeholder_label">Placeholder Label</label>
    <input type="text"
           name="placeholder_label"
           id="placeholder_label"
           class="form-control"
           value="{{ old('placeholder_label', $placeholderMapping->placeholder_label ?? '') }}"
           placeholder="ex: CPF/CNPJ do Proponente"
           required>
</div>

<div class="form-group mb-3">
    <label for="opportunity_id">Oportunidade</label>
    <select name="opportunity_id" id="opportunity_id" class="form-control" required>
        <option value="">-- selecione --</option>
        @foreach($opportunities as $op)
            <option value="{{ $op->id }}"
                {{ (old('opportunity_id', $placeholderMapping->opportunity_id ?? '') == $op->id) ? 'selected' : '' }}>
                {{ $op->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group mb-3">
    <label for="field_id">Campo do Edital</label>
    <select name="field_id" id="field_id" class="form-control" required>
        <option value="">-- selecione oportunidade antes --</option>
        @foreach($dynamicFields as $df)
            <option value="{{ $df->id }}"
                {{ (old('field_id', $placeholderMapping->field_id ?? '') == $df->id) ? 'selected' : '' }}>
                {{ $df->title }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group mb-3">
    <label for="priority">Prioridade</label>
    <input type="number"
           name="priority"
           id="priority"
           class="form-control"
           value="{{ old('priority', $placeholderMapping->priority ?? 1) }}"
           min="1"
           required>
</div>

<button type="submit" class="btn btn-primary">{{ $submitButtonText }}</button>

@push('js')
<script>
  $('#opportunity_id').on('change', function() {
    const parentId = $(this).val();
    $('#field_id').html('<option>Carregando...</option>');
    $.getJSON(`/admin/api/fields/${parentId}`, function(fields) {
      let opts = '<option value="">-- selecione --</option>';
      fields.forEach(f => {
        opts += `<option value="${f.id}">${f.title}</option>`;
      });
      $('#field_id').html(opts);
    });
  });
</script>
@endpush
