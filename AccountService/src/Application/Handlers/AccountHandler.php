<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Domain\Account\AccountDTO;
use App\Infrastructure\Persistence\Account\AccountRepositoryInterface;
use DomainException;
use App\Domain\Account\Account;
use App\Domain\Account\AccountNotFoundException;

class AccountHandler implements AccountHandlerInterface
{

    private AccountRepositoryInterface $accountRepository;
    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }
    public function getAllAccounts(): array
    {
     
        $accounts = $this->accountRepository->findAll();

        if ($accounts === null) {
            throw new DomainException("No accounts found tchusss");
        }

        $accountsDto  = array_map(
            fn($account) => new AccountDTO(
                $account
            ),
            $accounts
        );
    
            return array_map(fn($dto) => $dto->toArray(), $accountsDto);
    }

    /**
     * @throws AccountNotFoundException
     */
    public function getAccountById(int $id): AccountDTO
    {
        $account = $this->accountRepository->findById($id);

        if ($account === null) {
            throw new AccountNotFoundException("");
        }

        return new AccountDTO($account);
    }

    public function createAccount(array $data): AccountDTO
    {
        $account = new Account(
            null,
            $data['userId'],
            $data['userEmail'],
            $data['name'],
            $data['description'],
            true,
            null,
            null
        );
        $accountCreated = $this->accountRepository->createAccount(
            $account
        );
        
        return new AccountDTO(
            $accountCreated
        );
    }

    public function searchAccount($sql, $args = []): ?array
    {
        // TODO: Implement searchAccount() method.
    }

    public function updateAccount(array $data): bool
    {
        // TODO: Implement updateAccount() method.
    }

    public function deleteAccount($id): bool
    {
        // TODO: Implement deleteAccount() method.
    }
}
