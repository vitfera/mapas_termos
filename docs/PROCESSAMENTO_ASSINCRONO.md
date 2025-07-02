# Sistema de Geração de Termos - Processamento Assíncrono

Este documento explica a nova funcionalidade de geração assíncrona de termos em PDF.

## 🚀 O que mudou

### Problema anterior:
- Geração síncrona que travava o navegador
- Timeout do nginx após 2 minutos
- Usuário ficava sem feedback do progresso

### Solução implementada:
- ✅ **Processamento assíncrono** usando Laravel Queue
- ✅ **Resposta imediata** para o usuário
- ✅ **Acompanhamento em tempo real** com progress bar
- ✅ **Download automático** quando concluído
- ✅ **Histórico de processos** recentes

## 📋 Funcionalidades

### Interface do Usuário
1. **Formulário de seleção** - Escolhe edital e template
2. **Barra de progresso** - Acompanha geração em tempo real
3. **Status detalhado** - Mostra quantos termos foram processados
4. **Download automático** - Baixa ZIP quando concluído
5. **Histórico** - Lista processos recentes com possibilidade de re-download

### Backend
1. **Job assíncrono** - `GenerateTermsJob` processa em background
2. **Modelo de controle** - `TermGenerationProcess` gerencia estado
3. **API de status** - Endpoints para acompanhar progresso
4. **Worker persistente** - Configurado no Docker

## 🛠️ Arquivos modificados/criados

### Controllers
- `app/Http/Controllers/Admin/TermsController.php` - Modificado para processamento assíncrono

### Models
- `app/Models/TermGenerationProcess.php` - Novo modelo para controlar processos

### Jobs
- `app/Jobs/GenerateTermsJob.php` - Novo job para processamento assíncrono

### Views
- `resources/views/admin/terms/create.blade.php` - Interface renovada com progress bar
- `resources/views/admin/dashboard.blade.php` - Estatísticas dos processos

### Migrations
- `database/migrations/2025_07_02_000001_create_term_generation_processes_table.php`

### Docker/Deploy
- `coolify.yaml` - Serviço queue-worker configurado
- `docker/production/supervisord.conf` - Supervisor para workers
- `scripts/start-worker.sh` - Script para desenvolvimento local

### Frontend
- `public/css/terms-async.css` - Estilos para a nova interface

## 🔧 Como testar

### Desenvolvimento Local

1. **Preparar banco:**
```bash
# Dentro do container Docker:
php artisan migrate
```

2. **Iniciar worker:**
```bash
# Opção 1: Manual (para desenvolvimento)
./scripts/start-worker.sh

# Opção 2: Dentro do container
php artisan queue:work --verbose
```

3. **Testar interface:**
- Acesse `/admin/terms/create`
- Selecione um edital e template
- Observe a barra de progresso
- Baixe o arquivo quando concluído

### Homologação/Produção

1. **Deploy com Coolify:**
```bash
# O serviço queue-worker é iniciado automaticamente
docker-compose up -d
```

2. **Verificar workers:**
```bash
# Dentro do container
docker exec -it <container> ps aux | grep queue:work
```

## 📊 Monitoramento

### Dashboard
- Acesse `/admin` para ver estatísticas
- Processos em andamento
- Termos gerados hoje
- Histórico recente

### Logs
```bash
# Worker logs
tail -f storage/logs/worker.log

# Laravel logs
tail -f storage/logs/laravel.log
```

### Status de processos
```bash
# Dentro do container
php artisan queue:work --once  # Testa um job
php artisan queue:restart      # Reinicia workers
php artisan queue:failed       # Lista jobs falhados
```

## 🔄 APIs disponíveis

### Iniciar geração
```http
POST /admin/terms
Content-Type: application/json

{
  "opportunity_id": 123,
  "template_id": 456
}
```

### Verificar status
```http
GET /admin/terms/status?process_id=789
```

### Download
```http
GET /admin/terms/download?process_id=789
```

### Listar processos
```http
GET /admin/terms/processes
```

## ⚠️ Configurações importantes

### Queue Connection
No `.env`, certifique-se de ter:
```env
QUEUE_CONNECTION=database
```

### Worker Configuration
- **Timeout**: 3600s (1 hora)
- **Memory**: 512MB
- **Tries**: 3 tentativas
- **Sleep**: 3 segundos entre jobs

### Banco de dados
As tabelas de jobs são criadas automaticamente:
- `jobs` - Jobs pendentes
- `failed_jobs` - Jobs falhados
- `term_generation_processes` - Controle de processos

## 🐛 Troubleshooting

### Worker não processa jobs
```bash
# Verifica se o worker está rodando
ps aux | grep queue:work

# Reinicia worker
php artisan queue:restart

# Testa job manual
php artisan queue:work --once
```

### Jobs ficam pendentes
```bash
# Verifica fila
php artisan queue:monitor

# Limpa jobs travados
php artisan queue:clear
```

### Erro de timeout
```bash
# Aumenta timeout no nginx/php-fpm
# Ou verifica se o worker está rodando
```

## 📈 Performance

### Otimizações implementadas
- ✅ Processamento em lotes otimizado
- ✅ Cache de metadados durante geração
- ✅ Cleanup automático de arquivos antigos
- ✅ Progress tracking incremental

### Limitações atuais
- Máximo 1 processo por usuário simultaneamente
- Arquivos ZIP mantidos por 7 dias
- Worker restart diário para evitar memory leaks

## 🔄 Próximos passos

### Melhorias planejadas
- [ ] Notificações por email quando concluído
- [ ] Cancelamento de processos em andamento
- [ ] Processamento paralelo para editais grandes
- [ ] Limpeza automática de arquivos antigos
- [ ] Metrics/dashboard avançado

### Para implementar
1. Testes automatizados dos jobs
2. Monitoramento de performance
3. Alerts em caso de falhas
4. Backup automático dos ZIPs gerados
