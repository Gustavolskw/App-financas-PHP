<?php

namespace App\Infrastructure\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;

abstract class AMQPRepository
{
    protected AMQPStreamConnection $connection;
    protected AMQPChannel $channel;
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, AMQPStreamConnection $connection, AMQPChannel $channel)
    {
        $this->logger = $logger;
        $this->connection = $connection;
        $this->channel = $channel;
    }
}
