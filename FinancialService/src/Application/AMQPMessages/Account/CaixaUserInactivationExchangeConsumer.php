<?php

namespace App\Application\AMQPMessages\Account;

use App\Application\UseCases\Account\AccountStatusUpdaterCase;
use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\AMQP\AMQPRepository;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class CaixaUserInactivationExchangeConsumer extends AMQPRepository
{

    protected AccountRepository $accountRepository;
    protected AMQPChannel $channel;
    public function __construct(
        AccountRepository $accountRepository,
        LoggerInterface $logger,
        AMQPStreamConnection $connection,
    ) {
        parent::__construct($logger, $connection);
        $this->accountRepository = $accountRepository;
        $this->channel = $this->connection->channel();
    }
//exchange = 'auth.user.inactivated'
//queue = 'acc.user.inactivated'
    public function handleExchange(string $exchange, string $queue = 'acc.user.inactivated'): void
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
        $accountStatusChangeCase = new AccountStatusUpdaterCase($this->logger, $this->accountRepository);
        $accountStatusChangeCase->execute($data['userId'], false);
    }
}