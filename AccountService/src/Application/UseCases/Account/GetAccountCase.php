<?php

namespace App\Application\UseCases\Account;

use App\Application\UseCases\UseCase;
use App\Domain\DTO\AccountDTO;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Interfaces\AccountRepository;
use Psr\Log\LoggerInterface;

class GetAccountCase extends UseCase
{
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly AccountRepository $accountRepository
    ) {
        parent::__construct($logger, $accountRepository);
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function execute($id): ?AccountDTO
    {
        $this->logger->info("Get account");
        $account = $this->accountRepository->findById($id);
        if($account === null) {
            throw new ResourceNotFoundException();
        }
        return new AccountDTO($account);
    }
}