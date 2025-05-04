<?php

namespace App\Application\UseCases\Account;

use App\Application\Actions\Account\CreateAccountAction;
use App\Application\UseCases\UseCaseService;
use App\Domain\DTO\AccountDTO;
use App\Domain\Entity\Account;
use App\Domain\Interfaces\AccountRepository;
use Psr\Log\LoggerInterface;

class CreateAccountCase extends UseCaseService
{
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly AccountRepository $accountRepository
    ) {
        parent::__construct($logger, $accountRepository);
    }

    public function execute(array $accountData): AccountDTO
    {
        $this->logger->info("Create account");
        $newAccount = new Account(null,
            $accountData["userId"],
            $accountData["userEmail"],
            $accountData["name"],
            $accountData["description"],
            true,
            null,
            null );

        $account = $this->accountRepository->createAccount($newAccount);
        if ($account === null) {
            throw new \DomainException("Account not created");
        }

        return new AccountDTO($account);
    }

}