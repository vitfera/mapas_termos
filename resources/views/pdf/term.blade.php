<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <style>
    /** Define margens para dar espaço a header e footer **/
    @page {
      margin: 120px 50px 80px 50px;
    }

    /** Cabeçalho fixo **/
    header.pdf-header {
      position: fixed;
      top: -100px; /* - (altura do header) + espaçamento */
      left: 0;
      right: 0;
      height: 100px;
    }

    /** Rodapé fixo **/
    footer.pdf-footer {
      position: fixed;
      bottom: -60px; /* - (altura do footer) + espaçamento */
      left: 0;
      right: 0;
      height: 60px;
    }

    /** Corpo do documento **/
    .pdf-content {
      /* Qualquer estilização extra */
    }
  </style>
</head>
<body>
  <header class="pdf-header">
    {!! $header !!}
  </header>

  <footer class="pdf-footer">
    {!! $footer !!}
  </footer>

  <main class="pdf-content">
    {!! $body !!}
  </main>
</body>
</html>
