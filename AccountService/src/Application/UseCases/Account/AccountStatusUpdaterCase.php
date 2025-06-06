<?php

namespace App\Application\UseCases\Account;

use App\Application\UseCases\UseCase;
use App\Domain\Interfaces\AccountRepository;
use Psr\Log\LoggerInterface;

class AccountStatusUpdaterCase extends UseCase
{

    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly AccountRepository $accountRepository
    ) {
        parent::__construct($logger, $accountRepository);
    }

    public function execute(int $userId, bool $status): void
    {
        $this->logger->info("Update account status". $userId . " to " . $status);
        $this->accountRepository->updateUserAccountsStatus($userId, $status);
    }

}