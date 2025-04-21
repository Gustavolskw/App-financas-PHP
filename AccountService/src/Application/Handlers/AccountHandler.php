<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use Psr\Http\Message\ResponseInterface as Response;

class AccountHandler
{
    public function getAllAccounts(): array
    {
        // Simulate fetching accounts from a database or another source
        return [
            ['id' => 1, 'name' => 'Account 1'],
            ['id' => 2, 'name' => 'Account 2'],
            ['id' => 3, 'name' => 'Account 3'],
        ];
    }

    public function getAccountById(int $id): array
    {
        // Simulate fetching a single account by ID
        return ['id' => $id, 'name' => 'Account ' . $id];
    }
}
