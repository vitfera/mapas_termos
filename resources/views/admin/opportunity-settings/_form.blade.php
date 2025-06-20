{{-- Mostrar nome do Edital e enviar hidden --}}
<div class="form-group mb-3">
    <label>Edital</label>
    <input type="text"
            class="form-control-plaintext"
            value="{{ $opportunitySetting->opportunity->name }}"
            readonly>
    <input type="hidden" name="opportunity_id"
            value="{{ $opportunitySetting->opportunity_id }}">
</div>

{{-- Categoria --}}
<div class="form-group mb-3">
    <label for="category">Categoria</label>
    <select name="category"
            id="category"
            class="form-control @error('category') is-invalid @enderror"
            required>
        <option value="">-- selecione --</option>
        @foreach([
            'execucao'    => 'Execução',
            'premiacao'   => 'Premiação',
            'compromisso' => 'Compromisso'
        ] as $key => $label)
            <option value="{{ $key }}"
                {{ old('category', $opportunitySetting->category) === $key ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
    @error('category')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Número Inicial --}}
<div class="form-group mb-3">
    <label for="start_number">Número Inicial</label>
    <input type="number"
            name="start_number"
            id="start_number"
            class="form-control @error('start_number') is-invalid @enderror"
            value="{{ old('start_number', $opportunitySetting->start_number) }}"
            min="1"
            required>
    @error('start_number')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<button type="submit" class="btn btn-primary">{{ $submitButtonText }}</button>




