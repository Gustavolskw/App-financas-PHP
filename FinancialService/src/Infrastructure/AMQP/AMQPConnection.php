<?php

namespace App\Infrastructure\AMQP;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class AMQPConnection
{

    /**
     * @throws \Exception
     */
    public static function connect() :AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            getenv('RABBITMQ_HOST'),
            getenv('RABBITMQ_PORT'),
            getenv('RABBITMQ_USER'),
            getenv('RABBITMQ_PASS')
        );
    }

}