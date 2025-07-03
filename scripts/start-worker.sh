#!/bin/bash

# Script para rodar o worker de queue em DESENVOLVIMENTO LOCAL
# Para produção, use docker-compose.production.yml que já inclui o worker

echo "🚀 Iniciando worker de queue do Laravel (DESENVOLVIMENTO)..."
echo "📁 Diretório atual: $(pwd)"
echo "⏰ Iniciado em: $(date)"
echo ""
echo "⚠️  ATENÇÃO: Este script é apenas para desenvolvimento local!"
echo "   Para produção, use: docker-compose up -d (o coolify.yaml já inclui o queue-worker)"
echo ""

# Verifica se o artisan existe
if [ ! -f "artisan" ]; then
    echo "❌ Arquivo artisan não encontrado!"
    echo "   Execute este script no diretório raiz do projeto Laravel"
    exit 1
fi

# Limpa caches
echo "🧹 Limpando caches..."
php artisan config:clear
php artisan route:clear

# Roda as migrations se necessário
echo "📊 Verificando migrations..."
php artisan migrate --force

# Inicia o worker
echo "⚡ Iniciando queue worker..."
echo "   Pressione Ctrl+C para parar"
echo ""

# Roda o worker com configurações para desenvolvimento
php artisan queue:work \
    --sleep=3 \
    --tries=3 \
    --max-time=3600 \
    --timeout=3600 \
    --memory=512 \
    --verbose

echo ""
echo "🛑 Worker parado em: $(date)"
