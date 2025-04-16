#!/usr/bin/env php
<?php

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use parallel\{Runtime, Future};
use App\Application\Message\CaixaUserCreationQueueConsumer;
use App\Application\Message\CaixaUserInactivationExchangeConsumer;
use App\Application\Message\CaixaUserReactivationExchangeConsumer;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

function runConsumer($consumer) {
    $consumer->consume();
}

$future1 = (new Runtime())->run('runConsumer', [new CaixaUserCreationQueueConsumer()]);
$future2 = (new Runtime())->run('runConsumer', [new CaixaUserInactivationExchangeConsumer()]);
$future3 = (new Runtime())->run('runConsumer', [new CaixaUserReactivationExchangeConsumer()]);

$future1->value();
$future2->value();
$future3->value();
