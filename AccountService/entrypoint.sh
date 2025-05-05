#!/bin/bash
set -e
sleep 6
# Função para rodar um worker em segundo plano
start_worker() {
    echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Iniciando worker: $1"
    php /var/www/html/$1 &
    WORKER_PID=$!
    echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Worker $1 iniciado com PID $WORKER_PID"
}

# Executando migrações
echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Executando migrações do banco de dados..."
php /var/www/html/vendor/bin/doctrine-migrations migrations:migrate --no-interaction

# Iniciando os workers simultaneamente
start_worker "CaixaUserCreationQueueWorker.php"
start_worker "CaixaUserInactivationExchangeWorker.php"
start_worker "CaixaUserReactivationExchangeWorker.php"

# Iniciando Apache (processo principal) - Apache não será interrompido
echo "$(date "+%Y-%m-%d %H:%M:%S") INFO Iniciando Apache..."
exec apache2-foreground
