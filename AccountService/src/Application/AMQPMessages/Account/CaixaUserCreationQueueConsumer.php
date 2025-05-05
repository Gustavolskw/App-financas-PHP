<?php

namespace App\Application\AMQPMessages\Account;

use App\Application\UseCases\Account\CreateAccountCase;
use App\Domain\Exception\InvalidUserException;
use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\AMQP\AMQPRepository;
use GuzzleHttp\Exception\GuzzleException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class CaixaUserCreationQueueConsumer extends AMQPRepository
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

    public function handle(string $queue): void
    {
        $this->channel->queue_declare($queue, false, true, false, false);
        $callback = function (AMQPMessage $amqpMsg) {
            $data = json_decode($amqpMsg->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->logger->info("Criando caixa para o usuário com ID: " . $data['userId']);
            $this->createUserFirstAccount($data);
        };
          $this->channel->basic_consume($queue, '', false, true, false, false, $callback);
        while ($this->channel->is_consuming()) {
            $this->channel->wait();
        }
    }

    /**
     * @throws GuzzleException
     * @throws InvalidUserException
     */
    private function createUserFirstAccount(array $data): void
    {
        echo "Criando caixa para o usuário com ID: " . $data['userId'] . "\n";
        $createAccountCase = new CreateAccountCase(
            $this->logger,
            $this->accountRepository
        );
        $createAccountCase->execute($data, true);
    }
}
