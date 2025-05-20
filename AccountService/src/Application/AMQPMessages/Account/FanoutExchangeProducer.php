<?php

namespace App\Application\AMQPMessages\Account;

use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\AMQP\AMQPRepository;
use JsonException;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class FanoutExchangeProducer extends AMQPRepository
{

    protected AccountRepository $accountRepository;
    public function __construct(
        AccountRepository $accountRepository,
        LoggerInterface $logger,
        AMQPStreamConnection $connection,
    ) {
        parent::__construct($logger, $connection);
        $this->$accountRepository = $accountRepository;
    }
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