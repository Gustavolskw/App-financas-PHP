<?php

namespace App\Infrastructure\Persistence\Account;

use App\Domain\Account\Account;

interface AccountRepositoryInterface
{
    public function findAll(): ?array;

    public function findById($id): ?Account;

    public function searchedQuery($sql, $args = []): ?array;

    public function createAccount(Account $account): ?Account;

    public function updateAccount(Account $account, int $id): ?Account;

    public function deleteAccount(int $id): bool;

}