<?php

namespace App\Domain\Interfaces\DAO;

use App\Domain\Entity\Wallet;

interface WalletDAOInterface
{

    public function getWalletByUserEmailAndUserId(string $email, int $userId): ?Wallet;
    public function getWalletByUserId(int $userId): ?Wallet;
    public function getWalletByUserEmail(string $userEmail): ?Wallet;

}