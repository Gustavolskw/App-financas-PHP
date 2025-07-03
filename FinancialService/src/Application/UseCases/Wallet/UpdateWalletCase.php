<?php

namespace App\Application\UseCases\Wallet;

use App\Application\Exception\InvalidParametersDataException;
use App\Domain\Entity\Wallet;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Interfaces\Repository\PersistenceErrorLogRepositoryInterface;
use App\Domain\Interfaces\Repository\WalletRepositoryInterface;
use Monolog\Logger;
use Swoole\Coroutine;

class UpdateWalletCase
{
    public function __construct(private readonly WalletRepositoryInterface $walletRepository)
    {
    }



    public function execute($walletId, string $name, string $description): Wallet|string
    {
        $this->validateWalletIdParameter($walletId);
        $this->validateNameParameter($name);
        $this->validateDescriptionParameter($description);

        $wallet = null;

        Coroutine\run(function () use ($walletId, &$wallet) {
            $wallet = $this->validateWalletById($walletId);
        });

        $wallet->setName($name);
        $wallet->setDescription($description);
        $result = $this->walletRepository->update($wallet);
        if(!$result){
        return 'Failed to update wallet';
        }
        return $wallet;
    }

    private function validateWalletIdParameter($walletId): void
    {
        if (!is_int($walletId) || $walletId <= 0) {
            throw new InvalidParametersDataException('Invalid wallet ID');
        }
    }

    private function validateNameParameter(string $name): void
    {
        if (empty($name) || strlen($name) > 255) {
            throw new InvalidParametersDataException('Invalid wallet name');
        }
    }

    private function validateDescriptionParameter(string $description): void
    {
        if (empty($description) || strlen($description) > 500) {
            throw new InvalidParametersDataException('Invalid wallet description');
        }
    }

    private function validateWalletById(int $walletId): Wallet
    {
        $wallet = $this->walletRepository->getWalletById($walletId);
        if (!$wallet) {
            throw new ResourceNotFoundException("Wallet with ID $walletId not found");
        }
        return $wallet;
    }
}