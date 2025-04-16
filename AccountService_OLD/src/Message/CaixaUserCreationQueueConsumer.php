<?php

namespace Acc\Message;

use Acc\Services\AccountService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class CaixaUserCreationQueueConsumer
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

    // Consome a fila de criação de usuário
    public function consumeFromQueue(string $queue)
    {
        // Declara a fila, se ainda não existir
        $this->channel->queue_declare($queue, false, true, false, false);

        // Define a função de callback para processar as mensagens
        $callback = function (AMQPMessage $msg) {
            $data = json_decode($msg->getBody(), true);

            // Aqui você pode processar a mensagem para criação do caixa
            echo "Criando caixa para o usuário com ID: " . $data['userId'] . "\n";
            $this->createUserCashBox($data);
        };

        // Consome a fila
        $this->channel->basic_consume($queue, '', false, true, false, false, $callback);

        // Aguarda e processa mensagens
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function createUserCashBox(array $data)
    {
        echo "Criando caixa para o usuário com ID: " . $data['userId'] . "\n";

        $this->service->createNewUserAccount($data['userId'], $data['email']);
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
