<?php

namespace App\Application\Handlers;

use App\Domain\Account\AccountDTO;

interface AccountHandlerInterface
{
    public function getAllAccounts(): ?array;

    public function getAccountById(int $id): AccountDTO;

    public function searchAccount($sql, $args = []): ?array;

    public function createAccount(array $data): AccountDTO;

    public function updateAccount(array $data): bool;

    public function deleteAccount($id): bool;


}