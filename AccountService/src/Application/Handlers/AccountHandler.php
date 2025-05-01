<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Domain\Entity\Account;
use App\Domain\DTO\AccountDTO;
use App\Domain\Exception\AccountNotFoundException;
use App\Domain\Interfaces\AccountHandlerInterface;
use App\Domain\Interfaces\AccountRepository;
use DomainException;

class AccountHandler implements AccountHandlerInterface
{

    private AccountRepository $accountRepository;
    public function __construct(AccountRepository $accountRepository)
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

    public function createUserFirstAccount(array $data): void
    {


    }

    public function updateUserAccounts($id, $userEmail, bool $typeOfAction): void
    {
        // TODO: Implement updateUserAccounts() method.
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
}
