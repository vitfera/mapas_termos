# Changelog

Todas as mudanças notáveis neste projeto estão documentadas neste arquivo.

## [1.0.6] – 2025-06-23

### Adicionado
- Suporte ao placeholder `{{projeto}}`, substituído pelo `projectName` (chave do projeto) carregado do banco, com lookup em todas as fases (pai + filhas) no método `buildTermParts`.

## [1.0.5] – 2025-06-22

### Adicionado
- Injeção dinâmica da `licenseKey` do CKEditor via variável global `window.CKEDITOR_LICENSE_KEY`, permitindo uso de chaves diferentes em desenvolvimento e produção sem rebuild de assets.
- Exposição da chave correta no Blade antes do carregamento do `main.js`, usando `app()->environment()` para selecionar `CKEDITOR_LICENSE_PROD` ou `CKEDITOR_LICENSE_DEV`.

## [1.0.4] – 2025-06-22

### Adicionado
- Rota padrão `/admin` que redireciona para `/admin/dashboard`.  
- `DashboardController` com método `index()` e view `admin.dashboard`.  
- View Blade `resources/views/admin/dashboard.blade.php` com widgets de resumo (Templates, Mapeamentos, Configurações, Termos).  
- Nova rota `GET /admin/dashboard` nomeada `admin.dashboard`.  
- Item “Dashboard” adicionado ao menu lateral do AdminLTE (ícone `fas fa-tachometer-alt`).

## [1.0.3] – 2025-06-22

### Removido
- Todas as configurações de SSL do Nginx (Let’s Encrypt, certbot, portas 443/80)

### Adicionado
- Arquivos e scripts de infraestrutura para deployment em Coolify
- Configurações de Nginx, Docker e CI/CD ajustadas para ambiente de produção no Coolify

## [1.0.2] – 2025-06-20

### Adicionado
- Suporte a HTTPS via Let’s Encrypt (certbot)  
- Configuração de Nginx para servir em `https://sub.seudominio.com`  
- Atualização do `docker-compose.yml` com certificados e portas 443/80  

## [1.0.1] – 2025-06-20

### Adicionado

- Arquivo `version.txt` na raiz do projeto para armazenar a versão atual do sistema.  
- Rota de API `GET /info` que retorna em JSON:
  - Nome da aplicação (`app.name` ou “Mapas”)
  - Versão lida de `version.txt`
  - Ambiente de execução (`app()->environment()`)
  - Versão do PHP e do Laravel
  - Timezone configurada (`app.timezone`)

## [1.0.0] – 2025-06-20

### Adicionado

- **CRUD de Templates**  
  - Modelagem de templates com campos: `name`, `description`, `category` (execução, premiação, compromisso), `header_html`, `body_html`, `footer_html`.  
  - Integração com CKEditor 5 (CDN), suporte a imagens em Base64 e preview ao vivo.

- **Mapeamento de Placeholders**  
  - Entidade `placeholder_mappings` para associar cada placeholder (`{{nome}}`, `{{cpf}}`, etc.) a um campo dinâmico de edital ou agente.  
  - Prioridade configurável e carregamento dinâmico de campos via API interna.

- **Sincronização de Editais**  
  - Comando e botão “Sincronizar Editais” para importar oportunidades‐pai publicadas de um banco Postgres remoto sem duplicar IDs.  
  - Geração automática de registros em `opportunity_settings` com valores padrão.

- **Configurações de Edital**  
  - Entidade `opportunity_settings` para definir, por edital, categoria (execução | premiação | compromisso) e número inicial de termo.  
  - Lista paginada e edição inline no painel AdminLTE.

- **Geração de Termos em PDF**  
  - Controller `TermsController` para criar termos de execução/premiação/compromisso em lote ou individual.  
  - Cabeçalho e rodapé fixos em cada página via CSS `@page` e view Blade (`resources/views/pdf/term.blade.php`).  
  - Substituição de placeholders:
    - `{{campo}}` vindos de qualquer fase (pai + filhas) via `registration_meta` remoto.  
    - `{{id}}` sequência incremental unicamente por termo.  
    - `{{valor}}` – valor do projeto formatado em R$ e por extenso em português.  
  - Nome de arquivo padronizado: `term_{opportunityId}_{registrationNumber}_{agentSlug}.pdf`.  
  - ZIP automático ao gerar múltiplos termos.

- **API Dinâmica de Campos**  
  - Rota `GET /admin/api/fields/{parentId}` que retorna JSON com todos os campos (`id`, `title`) de uma oportunidade e suas fases‐filhas, para popular formulários via AJAX.

- **Integração AdminLTE**  
  - Menu lateral com seções: Templates, Mapeamentos, Configurações de Edital, Gerar Termos.  
  - Rotas protegidas sob prefixo `/admin`, sem autenticação por enquanto.

- **Ferramentas e Dependências**  
  - Laravel 12 e PHP 8.4.  
  - Banco principal: MySQL; conexão secundária: PostgreSQL (`pgsql_remote`).  
  - Extensões PHP: `pdo_mysql`, `pdo_pgsql`, `mbstring`, `gd`, `zip`, `intl`.  
  - Pacotes: `jeroennoten/laravel-adminlte`, `barryvdh/laravel-dompdf`.

---
