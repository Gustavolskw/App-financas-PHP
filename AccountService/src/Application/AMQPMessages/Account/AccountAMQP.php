<?php

namespace App\Application\AMQPMessages\Account;

use App\Application\Handlers\AccountHandler;
use App\Infrastructure\AMQP\AMQPConnection;

abstract class AccountAMQP extends AMQPConnection
{
    protected AccountHandler $handler;
    public function __construct()
    {
        parent::__construct();
        $this->handler = new AccountHandler();
    }
}
