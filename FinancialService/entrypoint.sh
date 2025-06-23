#!/bin/bash
set -e
if [ ! -d "vendor" ]; then
  echo "$(date "+%Y-%m-%d %H:%M:%S") WARN Pasta 'vendor' não encontrada. Executando 'composer install'..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
else
  echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Dependências já instaladas."
fi
sleep 6
echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Executando testes automatizados..."
vendor/bin/phpunit --testsuite=unit --testdox
start_worker() {
    echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Iniciando worker: $1"
    php /var/www/html/$1 &
    WORKER_PID=$!
    echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Worker $1 iniciado com PID $WORKER_PID"
}
echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Executando migrações do banco de dados..."
php /var/www/html/vendor/bin/doctrine-migrations migrations:migrate --no-interaction
start_worker "CaixaUserCreationQueueWorker.php"
start_worker "CaixaUserInactivationExchangeWorker.php"
start_worker "CaixaUserReactivationExchangeWorker.php"
echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Iniciando Apache..."
exec apache2-foreground