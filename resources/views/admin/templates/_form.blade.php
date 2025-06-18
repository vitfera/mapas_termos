@csrf

<div class="form-group mb-3">
    <label for="name">Nome</label>
    <input type="text" name="name" id="name" 
           class="form-control" 
           value="{{ old('name', $template->name ?? '') }}" 
           required>
</div>

<div class="form-group mb-3">
    <label for="description">Descrição</label>
    <textarea name="description" id="description" 
              class="form-control" rows="3">{{ old('description', $template->description ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <label for="category">Categoria</label>
    <select name="category" id="category" class="form-control" required>
        <option value="">-- selecione --</option>
        <option value="execucao" {{ old('category', $template->category ?? '')=='execucao'?'selected':'' }}>Execução</option>
        <option value="premiacao" {{ old('category', $template->category ?? '')=='premiacao'?'selected':'' }}>Premiação</option>
    </select>
</div>

<div class="form-group mb-3">
    <label for="header_html">Cabeçalho (HTML)</label>
    <textarea name="header_html" id="header_html" class="form-control" rows="4">{{ old('header_html', $template->header_html ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <label for="footer_html">Rodapé (HTML)</label>
    <textarea name="footer_html" id="footer_html" class="form-control" rows="4">{{ old('footer_html', $template->footer_html ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <label for="body_html">Corpo do Template (HTML)</label>
    <textarea name="body_html" id="body_html" class="form-control" rows="6" required>{{ old('body_html', $template->body_html ?? '') }}</textarea>
</div>

<button type="submit" class="btn btn-primary">
    {{ $submitButtonText }}
</button>
