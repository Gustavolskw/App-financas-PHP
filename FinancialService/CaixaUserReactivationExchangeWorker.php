#!/usr/bin/env php
<?php

echo "Iniciando worker...\n";

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/dependencies.php';

use App\Application\AMQPMessages\Account\CaixaUserReactivationExchangeConsumer;
use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\AMQP\AMQPConnection;
use DI\ContainerBuilder;
use Psr\Log\LoggerInterface;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$dependencies = require __DIR__ . '/app/dependencies.php';
$dependencies($containerBuilder);
try {
    $container = $containerBuilder->build();
} catch (Exception $e) {
    echo "Erro ao Buildar o CDI: " . $e->getMessage() . "\n";
    exit(1);
}

try {
    $connection = AMQPConnection::connect();
    echo "Conexão com RabbitMQ realizada com sucesso.\n";
} catch (\Throwable $exception) {
    echo "Erro ao conectar ao RabbitMQ: " . $exception->getMessage() . "\n";
    exit(1);
}

try {
    $repository = $container->get(AccountRepository::class);
    $logger = $container->get(LoggerInterface::class);
    $caixaConsumer = new CaixaUserReactivationExchangeConsumer($repository, $logger, $connection);

    echo "Consumidor criado, iniciando consumo de exchnage de Reativação...\n";
    $caixaConsumer->handleExchange(getenv("RABBITMQ_FAN_OUT_EXCHANGE_REACT"),'auth.user.reactivated' );

} catch (\Throwable $e) {
    echo "Erro no consumidor: " . $e->getMessage() . "\n";
    exit(1);
}
