<?php

namespace App\Domain\Interfaces;

use App\Domain\DTO\AccountDTO;

interface AccountHandlerInterface
{
    public function getAllAccounts(): ?array;

    public function getAccountById(int $id): AccountDTO;

    public function searchAccount($sql, $args = []): ?array;

    public function createAccount(array $data): AccountDTO;

    public function updateAccount(array $data): bool;

    public function deleteAccount($id): bool;
    public function createUserFirstAccount(array $data): void;
    public function updateUserAccounts($id, $userEmail, bool $typeOfAction):void;
}