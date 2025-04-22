<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Domain\Account\AccountDTO;
use App\Infrastructure\Persistence\Account\AcccountRepository;
use DomainException;
use App\Domain\Account\Account;


class AccountHandler
{

    private $accountRepository;
    public function __construct(AcccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }
    public function getAllAccounts()
    {   
     
        $accounts = $this->accountRepository->findAll();
        if ($accounts === null) {
            throw new DomainException("No accounts found tchusss");
        }

        $accountsDto  = array_map(
            fn($account) => new AccountDTO(
                $account
            ), $accounts);
           
            return $accountsDto;

    }

    public function getAccountById(int $id): array
    {
        // Simulate fetching a single account by ID
        return ['id' => $id, 'name' => 'Account ' . $id];
    }

    public function createAccount(array $data): AccountDTO
    {
        $account = new Account(
            null,
            $data['user_id'],
            $data['user_email'],
            $data['name'],
            $data['description'],
            $data['status'],
            null,
            null
        );
        $account = $this->accountRepository->createAccount(
            $account
        );

        return new AccountDTO(
            $account
        );
    }
}
