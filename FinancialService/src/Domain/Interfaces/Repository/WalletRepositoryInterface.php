<?php

namespace App\Domain\Interfaces\Repository;

use App\Domain\Entity\Wallet;

interface WalletRepositoryInterface
{
    public function save(Wallet $wallet): bool;
}