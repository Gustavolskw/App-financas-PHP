#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function fork(callable $callback): void
{
    $pid = pcntl_fork();
    if ($pid == -1) {
        die('Erro ao criar processo');
    } elseif ($pid === 0) {
        $callback();
        exit(0);
    }
}

fork(function () {
    $consumer = new CaixaUserCreationQueueConsumer();
    $consumer->consumeFromQueue($_ENV['RABBITMQ_CONSUME_QUEUE']);
});

fork(function () {
    $consumer = new CaixaUserInactivationExchangeConsumer();
    $consumer->consumeFromExchange();
});

fork(function () {
    $consumer = new CaixaUserReactivationExchangeConsumer();
    $consumer->consumeFromExchange();
});

// Aguarda todos os filhos
while (pcntl_wait($status) != -1)
    ;