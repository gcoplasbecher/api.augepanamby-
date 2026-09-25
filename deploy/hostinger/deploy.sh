#!/usr/bin/env bash
set -euo pipefail

# ==============================================================================
# Script de Deploy Idempotente — Auge Panamby API (Hostinger)
# ==============================================================================

# Binários fixados para Hostinger CloudLinux
# ATENÇÃO: o composer.lock exige PHP >= 8.4.1, por isso o binário padrão é o php84.
# (o /usr/bin/php e o /opt/alt/php83 não atendem a esse requisito)
PHP_BIN="/opt/alt/php84/usr/bin/php"
COMPOSER_BIN="/usr/local/bin/composer"

if [[ ! -x "$PHP_BIN" ]]; then
    # Fallback para outras versões alt disponíveis na CloudLinux
    for CANDIDATE in /opt/alt/php85/usr/bin/php /opt/alt/php83/usr/bin/php; do
        if [[ -x "$CANDIDATE" ]]; then
            PHP_BIN="$CANDIDATE"
            break
        fi
    done
fi

if [[ ! -x "$PHP_BIN" ]]; then
    PHP_BIN="$(which php)"
fi

if [[ ! -x "$COMPOSER_BIN" ]]; then
    COMPOSER_BIN="$(which composer)"
fi

# Diretórios
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_DIR="$(cd "$SCRIPT_DIR/../.." && pwd)"
DOMAIN_DIR="$(cd "$APP_DIR/.." && pwd)"
PUBLIC_HTML="$DOMAIN_DIR/public_html"

echo "=========================================="
echo "Iniciando Deploy da API Auge Panamby"
echo "Data: $(date '+%Y-%m-%d %H:%M:%S')"
echo "PHP: $("$PHP_BIN" -v | head -1)"
echo "Composer: $("$COMPOSER_BIN" --version 2>/dev/null | head -1)"
echo "App Dir: $APP_DIR"
echo "Public HTML: $PUBLIC_HTML"
echo "=========================================="

cd "$APP_DIR"

# 1. Puxar últimas alterações do Git se estiver em um repositório
if [[ -d ".git" ]]; then
    echo ">> Atualizando repositório via Git..."
    git pull origin main || echo "Aviso: git pull falhou ou não há upstream configurado, continuando com arquivos existentes."
fi

# 2. Instalar dependências de produção sem dev
echo ">> Instalando dependências Composer..."
"$PHP_BIN" "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction

# 3. Verificar arquivo .env
if [[ ! -f ".env" ]]; then
    echo ">> Arquivo .env não encontrado. Copiando de .env.example..."
    cp .env.example .env
    "$PHP_BIN" artisan key:generate --force
    echo "ATENÇÃO: Um novo .env foi gerado. Configure os dados do banco MySQL e SMTP antes de continuar."
fi

# 4. Executar migrations
echo ">> Executando migrações do banco de dados..."
"$PHP_BIN" artisan migrate --force

# 5. Otimizar e atualizar caches
echo ">> Limpando e recriando caches do Laravel..."
"$PHP_BIN" artisan config:clear
"$PHP_BIN" artisan route:clear
"$PHP_BIN" artisan config:cache
"$PHP_BIN" artisan route:cache

# 6. Sincronizar arquivos do docroot público (public_html)
echo ">> Sincronizando public_html..."
mkdir -p "$PUBLIC_HTML"
rm -f "$PUBLIC_HTML/default.php"

cp "$SCRIPT_DIR/index.php" "$PUBLIC_HTML/index.php"
cp "$SCRIPT_DIR/public_html.htaccess" "$PUBLIC_HTML/.htaccess"

# Se houver favicon ou robots no public do Laravel, sincroniza também
if [[ -f "$APP_DIR/public/favicon.ico" ]]; then
    cp "$APP_DIR/public/favicon.ico" "$PUBLIC_HTML/favicon.ico"
fi
if [[ -f "$APP_DIR/public/robots.txt" ]]; then
    cp "$APP_DIR/public/robots.txt" "$PUBLIC_HTML/robots.txt"
fi

# 7. Ajustar permissões para o servidor web
# Diretórios 775 e arquivos 664: aplicar 775 recursivamente também deixaria os
# arquivos executáveis, o que marca .gitignore como modificado no git a cada deploy.
echo ">> Ajustando permissões de storage e bootstrap/cache..."
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type d -exec chmod 775 {} +
find "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" -type f -exec chmod 664 {} +

echo "=========================================="
echo "Deploy finalizado com sucesso!"
echo "Verifique o healthcheck em: https://api.augepanamby.net.br/up"
echo "=========================================="
