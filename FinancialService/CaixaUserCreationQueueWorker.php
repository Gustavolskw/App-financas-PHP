#!/usr/bin/env php
<?php

echo "Iniciando worker...\n";

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/dependencies.php';

use App\Application\AMQPMessages\Account\CaixaUserCreationQueueConsumer;
use App\Infrastructure\AMQP\AMQPConnection;
use DI\ContainerBuilder;

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
    $repository = $container->get(\App\Domain\Interfaces\AccountRepository::class);
    $logger = $container->get(\Psr\Log\LoggerInterface::class);
    $caixaConsumer = new CaixaUserCreationQueueConsumer($repository, $logger, $connection);

    echo "Consumidor criado, iniciando consumo de conta nova...\n";
    $caixaConsumer->handle(getenv("RABBITMQ_CONSUME_QUEUE"));

} catch (\Throwable $e) {
    echo "Erro no consumidor: " . $e->getMessage() . "\n";
    exit(1);
}
