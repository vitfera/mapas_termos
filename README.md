# Termos Culturais

Um sistema em Laravel 12 para gerenciar modelos de termos de Execução, Premiação e Compromisso Cultural, sincronizar editais de um banco Postgres externo, mapear campos dinâmicos e gerar PDFs (via Dompdf) com placeholders substituídos.

---

## ✨ Funcionalidades

- **CRUD de Templates** (nome, descrição, categoria, cabeçalho, corpo e rodapé em HTML WYSIWYG)
- **Mapeamento de Placeholders** para cada edital (campo dinâmico, tipo de fonte, prioridade)
- **Sincronização de Editais** (apenas leitura de `opportunity` do Postgres remoto)
- **Configurações de Edital** (categoria, número inicial de termo)
- **Geração de Termos** em PDF (unitário ou ZIP), com:
  - Substituição de `{{campo}}`, `{{ id }}` e `{{ valor }}`
  - Cabeçalho/Rodapé fixos em cada página
  - Nome do arquivo incluindo número de inscrição e nome do proponente

---

## 📋 Pré-requisitos

- PHP 8.4+
- Composer
- MySQL (aplicação principal)
- PostgreSQL (banco `pgsql_remote` com tabelas `opportunity`, `registration`, `registration_meta`, `agent`, etc.)
- Extensões PHP: `pdo_mysql`, `pdo_pgsql`, `mbstring`, `gd`, `zip`, `intl`
- Dompdf (`barryvdh/laravel-dompdf`)
- [Laravel AdminLTE](https://github.com/jeroennoten/Laravel-AdminLTE)
- CKEditor 5 (CDN)

---

## 🚀 Instalação

1. Clone o repositório  
   ```bash
   git clone https://github.com/vitfera/mapas_termos.git
   cd mapas_termos
