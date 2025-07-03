#!/bin/bash

# Script para commit das alterações de processamento assíncrono

echo "📝 Commitando alterações do processamento assíncrono..."

# Adiciona todos os arquivos modificados
git add .

# Commit com mensagem descritiva
git commit -m "feat: implementa geração assíncrona de termos em PDF

- Adiciona processamento assíncrono usando Laravel Queue
- Cria interface com progress bar em tempo real  
- Implementa modelo TermGenerationProcess para controle
- Adiciona job GenerateTermsJob para processamento em background
- Atualiza controller com endpoints de status e download
- Configura queue-worker no coolify.yaml para produção
- Adiciona dashboard com estatísticas de processos
- Cria documentação completa do sistema

Resolve problema de timeout do nginx (2min) permitindo:
- Resposta imediata ao usuário
- Acompanhamento em tempo real
- Download quando concluído
- Histórico de processos recentes

Tecnologias: Laravel Queue, Jobs, Ajax polling, Progress bars"

echo "✅ Commit realizado com sucesso!"
echo ""
echo "📋 Próximos passos:"
echo "1. Testar localmente com: ./scripts/start-worker.sh"
echo "2. Fazer push: git push origin feat/async-pdf-generation"
echo "3. Testar em homologação"
echo "4. Fazer merge para develop quando estável"
