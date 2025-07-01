<?php

namespace App\Application\UseCases\Wallet;

use App\Application\Exception\InvalidParametersDataException;
use App\Domain\Interfaces\DAO\WalletDAOInterface;
use App\Domain\Interfaces\Repository\WalletRepositoryInterface;

class CreateWalletCase
{
    public function __construct(private readonly WalletRepositoryInterface $walletRepository, private readonly WalletDAOInterface $walletDAO)
    {
    }


    public function execute(int $userId, string $userEmail):void
    {


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

    public function validateWalletByUserAndEmail(string $userEmail): void
    {

    }

}