@csrf

<div class="form-group mb-3">
    <label for="template_id">Template</label>
    <select name="template_id" id="template_id" class="form-control" required>
        <option value="">-- selecione --</option>
        @foreach($templates as $t)
            <option value="{{ $t->id }}"
                {{ old('template_id', $templatePlaceholder->template_id ?? '') == $t->id ? 'selected' : '' }}>
                {{ $t->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group mb-3">
    <label for="key">Key</label>
    <input type="text" name="key" id="key" 
           class="form-control"
           value="{{ old('key', $templatePlaceholder->key ?? '') }}"
           required>
</div>

<div class="form-group mb-3">
    <label for="label">Label</label>
    <input type="text" name="label" id="label" 
           class="form-control"
           value="{{ old('label', $templatePlaceholder->label ?? '') }}"
           required>
</div>

<button type="submit" class="btn btn-primary">{{ $submitButtonText }}</button>
