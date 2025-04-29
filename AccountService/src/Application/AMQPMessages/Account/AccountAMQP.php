<?php

namespace App\Application\AMQPMessages\Account;

use App\Application\Handlers\AccountHandlerInterface;
use App\Infrastructure\AMQP\AMQPRepository;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;

abstract class AccountAMQP extends AMQPRepository
{
    protected AccountHandlerInterface $handler;
    public function __construct(
        AccountHandlerInterface $handler,
        LoggerInterface $logger,
        AMQPStreamConnection $connection,
        AMQPChannel $channel
    ) {
        parent::__construct($logger, $connection, $channel);
        $this->handler = $handler;
    }

//    abstract public function publish(string $exchange, string $message): void;
    abstract public function handle(?string $exchange, ?string $queue, ?string $message, ?array $payload): void;
//    abstract public function consumeFromQueue(string $queue): void;
}
