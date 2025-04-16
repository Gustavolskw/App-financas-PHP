<?php

namespace Acc\Message;

use Acc\Services\AccountService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CaixaUserReactivationExchangeConsumer
{
    private $connection;
    private $channel;

    private $service;

    public function __construct()
    {
        // Conexão com o RabbitMQ
        $this->connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASS'],
            $_ENV['RABBITMQ_VHOST']
        );

        $this->channel = $this->connection->channel();
        $this->service = new AccountService();
    }

    // Consome mensagens da exchange auth.user.reactivated
    public function consumeFromExchange()
    {
        $this->channel->exchange_declare('auth.user.reactivated', 'fanout', false, true, false);

        $queue = 'acc.user.reactivated';

        $this->channel->queue_declare($queue, false, true, false, false);

        $this->channel->queue_bind($queue, 'auth.user.reactivated');

        $callback = function (AMQPMessage $msg) {
            $data = json_decode($msg->getBody(), true);
            $this->processUserReactivation($data);
        };

        $this->channel->basic_consume($queue, '', false, true, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function processUserReactivation(array $data)
    {
        // Implementação para processar a reativação do usuário
        echo "Reativando contas do usuário com ID: " . $data['userId'] . "\n";

        $this->service->reactivateUserAccounts($data['userId']);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
