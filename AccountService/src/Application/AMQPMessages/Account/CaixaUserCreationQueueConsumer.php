<?php

namespace App\Application\AMQPMessages\Account;

use PhpAmqpLib\Message\AMQPMessage;

class CaixaUserCreationQueueConsumer extends AccountAMQP
{

    public function handle(?string $exchange, ?string $queue, ?string $message, ?array $payload): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);
        $callback = function (AMQPMessage $amqpMsg) {
            $data = json_decode($amqpMsg->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->info("Criando caixa para o usuário com ID: ", $data['userId']);
        };
          $this->channel->basic_consume($queue, '', false, true, false, false, $callback);
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function createUserFirstAccount(array $data)
    {
        echo "Criando caixa para o usuário com ID: " . $data['userId'] . "\n";

        //$this->service->createNewUserAccount($data['userId'], $data['email']);
    }
}
