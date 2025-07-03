<?php

namespace App\Application\UseCases\Wallet;

use App\Application\Exception\InvalidParametersDataException;
use App\Application\Exception\SaveResourceException;
use App\Domain\Entity\Wallet;
use App\Domain\Interfaces\DAO\WalletDAOInterface;
use App\Domain\Interfaces\Repository\PersistenceErrorLogRepositoryInterface;
use App\Domain\Interfaces\Repository\WalletRepositoryInterface;
use DateTime;
use Monolog\Logger;
use Swoole\Coroutine;

class CreateWalletCase
{
    public function __construct(private readonly WalletRepositoryInterface $walletRepository,
                                private readonly WalletDAOInterface $walletDAO,
                                private readonly Logger $logger,
                                private readonly PersistenceErrorLogRepositoryInterface $persistenceErrorLogRepository)
    {
    }


    public function execute(int $userId, string $userEmail):void
    {
        $this->validateUserIdParameter($userId);
        $this->validateEmailParameter($userEmail);
        $this->validateWalletByUserAndEmail($userEmail, $userId);

        $newWallet = new Wallet(
            null,
            $userId,
            $userEmail,
            "Carteira do usuário $userId",
            "Carteira criada automaticamente para o usuário $userId com email $userEmail",
            true,
            new DateTime('now', new \DateTimeZone('America/Sao_Paulo'))
        );
        $saveResult = $this->walletRepository->save($newWallet);
        if(!$saveResult){
            Coroutine::create(function () use ($newWallet, $userId, $userEmail) {
                $this->logger->critical("Failed to save wallet for user with ID $userId and email $userEmail");
                $this->persistenceErrorLogRepository->save([
                    'message' => "Failed to save wallet for user with ID $userId and email $userEmail",
                    'context' => $newWallet,
                    'timestamp' => (new DateTime('now', new \DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s'),
                ]);
            });
            throw new SaveResourceException("Failed to save wallet for user with ID $userId and email $userEmail", []);
        }
    }

    public function validateEmailParameter(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidParametersDataException('Invalid email format', []);
        }
    }
    public function validateUserIdParameter(int $userId): void
    {
        if ($userId <= 0) {
            throw new InvalidParametersDataException('User ID must be a positive integer', []);
        }
    }

    public function validateWalletByUserAndEmail(string $userEmail, int $userId): void
    {
        $wallet = $this->walletDAO->getWalletByUserEmailAndUserId($userEmail, $userId);
        if(!empty($wallet)){
            throw new InvalidParametersDataException('Wallet already exists for this user and email', []);
        }
    }

}