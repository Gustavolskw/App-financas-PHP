<?php

namespace App\Application\UseCases\Account;

use App\Application\UseCases\UseCase;
use App\Domain\DTO\AccountDTO;
use App\Domain\Interfaces\AccountRepository;
use JsonException;
use Psr\Log\LoggerInterface;

class UpdateAccountCase extends UseCase
{

    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly AccountRepository $accountRepository
    )
    {
        parent::__construct($logger, $accountRepository);
    }

    /**
     * @throws JsonException
     */
    public function execute(array $accountData, int $accountId)
    {
        $this->logger->info("Update account" . json_encode($accountData, JSON_THROW_ON_ERROR));
        $account = $this->accountRepository->findById($accountId);
        if ($account === null) {
            throw new \DomainException("Account not found");
        }
        $account->setName($accountData["name"] ?? $account->getName());
        $account->setDescription($accountData["description"] ?? $account->getDescription());
        $updatedAccount = $this->accountRepository->updateAccount($account, $accountId);
        return new AccountDTO($updatedAccount);
    }
}