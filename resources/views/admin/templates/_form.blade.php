@csrf

<div class="form-group mb-3">
    <label for="name">Nome</label>
    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $template->name ?? '') }}" required>
</div>

<div class="form-group mb-3">
    <label for="description">Descrição</label>
    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $template->description ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <label for="header_html">Cabeçalho (HTML)</label>
    <input type="hidden" name="header_html" id="header_html_input" value="{{ old('header_html', $template->header_html ?? '') }}">
    <textarea id="header_html" class="form-control wysiwyg" rows="4">{{ old('header_html', $template->header_html ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <label for="footer_html">Rodapé (HTML)</label>
    <input type="hidden" name="footer_html" id="footer_html_input" value="{{ old('footer_html', $template->footer_html ?? '') }}">
    <textarea id="footer_html" class="form-control wysiwyg" rows="4">{{ old('footer_html', $template->footer_html ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <label for="body_html">Corpo do Template (HTML)</label>
    <input type="hidden" name="body_html" id="body_html_input" value="{{ old('body_html', $template->body_html ?? '') }}">
    <textarea id="body_html" class="form-control wysiwyg" rows="6">{{ old('body_html', $template->body_html ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <button type="submit" class="btn btn-primary">
        {{ $submitButtonText }}
    </button>
    <button type="button" class="btn btn-secondary ml-2" id="previewTemplateBtn">
        Visualizar Preview
    </button>
</div>

{{-- Modal de Preview (Bootstrap 4) --}}
<div class="modal fade" id="templatePreviewModal" tabindex="-1" role="dialog" aria-labelledby="templatePreviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="templatePreviewModalLabel">Pré-visualização do Template</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="templatePreviewContent" style="min-height:200px;">
        {{-- Conteúdo será injetado via JavaScript --}}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

@push('css')
    <!-- Seu CSS customizado do Builder -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <!-- CSS do CKEditor custom CDN (versão 45.2.0) -->
    <link rel="stylesheet" 
          href="https://cdn.ckeditor.com/ckeditor5/45.2.0/ckeditor5.css" 
          crossorigin>
@endpush

@push('js')
    <!-- CKEditor custom build UMD -->
    <script src="https://cdn.ckeditor.com/ckeditor5/45.2.0/ckeditor5.umd.js" crossorigin></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/45.2.0/translations/pt-br.umd.js" crossorigin></script>
    <!-- Sua configuração gerada -->
    <script src="{{ asset('js/main.js') }}"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // A build custom já expõe ClassicEditor globalmente
        const editors = {};

        // Inicializa cada textarea wysiwyg
        document.querySelectorAll('.wysiwyg').forEach(textarea => {
            ClassicEditor
                .create(textarea, editorConfig)    // editorConfig vem de main.js
                .then(editor => {
                    // Adapta o upload para Base64
                    editor.plugins.get('FileRepository').createUploadAdapter = loader => ({
                        upload() {
                            return loader.file.then(file => new Promise((res, rej) => {
                                const r = new FileReader();
                                r.onload  = () => res({ default: r.result });
                                r.onerror = err => rej(err);
                                r.readAsDataURL(file);
                            }));
                        },
                        abort() {}
                    });
                    editors[textarea.id] = editor;
                })
                .catch(console.error);
        });

        // Sincroniza hidden fields antes do submit
        document.getElementById('templateForm').addEventListener('submit', () => {
            ['header_html','body_html','footer_html'].forEach(id => {
                document.getElementById(id + '_input').value = editors[id].getData();
            });
        });

        // Botão de preview
        document.getElementById('previewTemplateBtn')
            .addEventListener('click', () => {
                const html = ['header_html','body_html','footer_html']
                    .map(id => editors[id]?.getData() || '')
                    .join('');
                document.getElementById('templatePreviewContent').innerHTML = html;
                $('#templatePreviewModal').modal('show');
            });
    });
    </script>
@endpush
