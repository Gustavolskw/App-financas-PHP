<?php

namespace App\Domain\Interfaces;

use App\Domain\Entity\Account;

interface AccountRepository extends RepositoryInterface
{
    public function findAll(): ?array;

    public function findById($id): ?Account;

    public function searchedQuery($sql, $args = []): ?array;

    public function countUserAccounts(int $userId):mixed;

    public function createAccount(Account $account): ?Account;

    public function updateAccount(Account $account, int $id): ?Account;

    public function deleteAccount(int $id): bool;

    public function deleteAccountsByUserId(int $userId, string $userEmail): bool;

    public function veirifAccount(int $id): bool;
    public function updateUserAccountsStatus(int $userId, bool $status):void;

}