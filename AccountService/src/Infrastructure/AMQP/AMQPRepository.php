<?php

namespace App\Infrastructure\AMQP;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Log\LoggerInterface;

abstract class AMQPRepository
{
    protected AMQPStreamConnection $connection;
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, AMQPStreamConnection $connection)
    {
        $this->logger = $logger;
        $this->connection = $connection;
    }
}
