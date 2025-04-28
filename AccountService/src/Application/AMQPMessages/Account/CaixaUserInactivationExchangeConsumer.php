<?php

namespace App\Application\AMQPMessages\Account;

use PhpAmqpLib\Message\AMQPMessage;

class CaixaUserInactivationExchangeConsumer extends AccountAMQP
{
//exchange = 'auth.user.inactivated'
//queue = 'acc.user.inactivated'
    public function handle(?string $exchange, ?string $queue, ?string $message, ?array $payload): void
    {
        $this->channel->exchange_declare($exchange, 'fanout', false, true, false);



        $this->channel->queue_declare($queue, false, true, false, false);

        $this->channel->queue_bind($queue, $exchange);

        $callback = function (AMQPMessage $msg) {
            $data = json_decode($msg->getBody(), true, 512, JSON_THROW_ON_ERROR);

            $this->processUserInactivation($data);
        };

        $this->channel->basic_consume($queue, '', false, true, false, false, $callback);

        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    private function processUserInactivation(array $data)
    {
        $this->logger->info("Inativando Contas do usuário com ID: " . $data['userId']);
        //$this->service->inactivateUserAccounts($data['userId']);
    }
}