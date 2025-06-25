<?php

namespace App\Application\UseCases\Wallet;

use App\Domain\Interfaces\WalletRepository;

class CreateWalletCase
{
    public function __construct(private readonly WalletRepository $walletRepository)
    {
    }


    public function execute(int $userId, string $userEmail){

    }

}