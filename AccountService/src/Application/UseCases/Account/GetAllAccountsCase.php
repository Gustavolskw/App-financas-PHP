<?php

namespace App\Application\UseCases\Account;

use App\Application\UseCases\UseCaseService;
use App\Domain\DTO\AccountDTO;
use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\Persistence\Account\PdoAccountRepository;
use DomainException;
use Psr\Log\LoggerInterface;

class GetAllAccountsCase extends UseCaseService
{
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly AccountRepository $accountRepository
    ) {
        parent::__construct($logger, $accountRepository);
    }
    public function execute(): ?array
    {
        $this->logger->info("Get all accounts");
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


}