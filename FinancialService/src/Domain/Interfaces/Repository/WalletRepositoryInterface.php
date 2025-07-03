<?php

namespace App\Domain\Interfaces\Repository;

use App\Domain\Entity\Wallet;

interface WalletRepositoryInterface
{
    public function save(Wallet $wallet): bool;
    public function update(Wallet $wallet): bool;
    public function inactivate(int $walletId): bool;
    public function activate(int $walletId): bool;
    public function getWalletById(int $walletId): ?Wallet;
    public function getAllWallets():array;
}