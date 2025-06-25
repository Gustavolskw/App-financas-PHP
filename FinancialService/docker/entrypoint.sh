#!/bin/bash
set -e
if [ ! -d "vendor" ]; then
  echo "$(date "+%Y-%m-%d %H:%M:%S") WARN Pasta 'vendor' não encontrada. Executando 'composer install'..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
else
  echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Dependências já instaladas."
fi

echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Executando migrações do banco de dados..."
php /app/vendor/bin/doctrine-migrations migrations:migrate --no-interaction

echo "Iniciando FrankenPHP..."
frankenphp run --config /etc/caddy/Caddyfile --adapter caddyfile

