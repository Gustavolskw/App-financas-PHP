<?php

namespace App\Application\AMQPMessages\Account;

use JsonException;
use PhpAmqpLib\Message\AMQPMessage;

class FanoutExchangeProducer extends AccountAMQP
{

    /**
     * @throws JsonException
     */
    public function handle(?string $exchange, ?string $queue, ?string $message, ?array $payload): void
    {
        $this->channel->exchange_declare($exchange, 'fanout', false, true, false);

        $newMessage = new AMQPMessage(json_encode($payload, JSON_THROW_ON_ERROR), [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            'content_type' => 'application/json'
        ]);

        $this->channel->basic_publish($newMessage, $exchange);
    }
}