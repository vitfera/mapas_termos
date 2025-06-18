@csrf

<div class="form-group mb-3">
    <label for="placeholder_id">Placeholder</label>
    <select name="placeholder_id" id="placeholder_id" class="form-control" required>
        <option value="">-- selecione --</option>
        @foreach($placeholders as $ph)
            <option value="{{ $ph->id }}"
                {{ old('placeholder_id', $placeholderMapping->placeholder_id ?? '') == $ph->id ? 'selected' : '' }}>
                {{ $ph->key }} ({{ $ph->template->name }})
            </option>
        @endforeach
    </select>
</div>

<div class="form-group mb-3">
    <label for="opportunity_id">Oportunidade (ID)</label>
    <select name="opportunity_id" id="opportunity_id" class="form-control" required>
        <option value="">-- selecione --</option>
        @foreach($opportunities as $op)
            <option value="{{ $op->id }}"
                {{ old('opportunity_id', $placeholderMapping->opportunity_id ?? '') == $op->id ? 'selected' : '' }}>
                [{{ $op->id }}] {{ $op->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group mb-3">
    <label for="source_type">Tipo de Fonte</label>
    <select name="source_type" id="source_type" class="form-control" required>
        <option value="">-- selecione --</option>
        @foreach($sourceTypes as $key => $label)
            <option value="{{ $key }}"
                {{ old('source_type', $placeholderMapping->source_type ?? '') == $key ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group mb-3">
    <label for="source_key">Source Key</label>
    <input type="text" name="source_key" id="source_key" 
           class="form-control"
           value="{{ old('source_key', $placeholderMapping->source_key ?? '') }}"
           required>
</div>

<div class="form-group mb-3">
    <label for="priority">Prioridade</label>
    <input type="number" name="priority" id="priority"
           class="form-control"
           value="{{ old('priority', $placeholderMapping->priority ?? 1) }}"
           min="1" required>
</div>

<button type="submit" class="btn btn-primary">{{ $submitButtonText }}</button>
