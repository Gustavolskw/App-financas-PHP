<?php

namespace Acc\Message;

use Acc\Services\AccountService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CaixaUserInactivationExchangeConsumer
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
    public function consumeFromExchange()
    {

        $this->channel->exchange_declare('auth.user.inactivated', 'fanout', false, true, false);

        $queue = 'acc.user.inactivated';

        $this->channel->queue_declare($queue, false, true, false, false);

        $this->channel->queue_bind($queue, 'auth.user.inactivated');

        $callback = function (AMQPMessage $msg) {
            $data = json_decode($msg->getBody(), true);

            $this->processUserInactivation($data);
        };

        $this->channel->basic_consume($queue, '', false, true, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }



    private function processUserInactivation(array $data)
    {
        echo "Inativando Contas do usuário com ID: " . $data['userId'] . "\n";
        $this->service->inactivateUserAccounts($data['userId']);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
