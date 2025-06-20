import ClassicEditorBase from '@ckeditor/ckeditor5-build-classic';
import Alignment from '@ckeditor/ckeditor5-alignment/src/alignment';
import Base64UploadAdapter from '@ckeditor/ckeditor5-upload/src/adapters/base64uploadadapter';

export default class ClassicEditor extends ClassicEditorBase {}

// Injeta os plugins adicionais
ClassicEditor.builtinPlugins = [
  ...ClassicEditorBase.builtinPlugins,
  Alignment,
  Base64UploadAdapter
];

// Define a toolbar e config
ClassicEditor.defaultConfig = {
  toolbar: {
    items: [
      'heading','|',
      'alignment:left','alignment:center','alignment:right','alignment:justify','|',
      'bold','italic','underline','strikethrough','|',
      'link','bulletedList','numberedList','|',
      'blockQuote','|',
      'undo','redo','|',
      'imageUpload'
    ]
  },
  alignment: {
    options: [ 'left','center','right','justify' ]
  },
  image: {
    toolbar: [ 'imageTextAlternative' ]
  },
  language: 'pt-br'
};
