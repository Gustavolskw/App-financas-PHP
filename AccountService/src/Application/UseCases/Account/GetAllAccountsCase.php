<?php

namespace App\Application\UseCases\Account;

use App\Application\UseCases\UseCaseService;
use App\Domain\DTO\AccountDTO;
use App\Domain\Exception\ResourceNotFoundException;
use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\Persistence\Account\PdoAccountRepository;
use DomainException;
use Psr\Log\LoggerInterface;

class GetAllAccountsCase extends UseCaseService
{
    public function __construct(
        private readonly LoggerInterface   $logger,
        AccountRepository $accountRepository
    ) {
        parent::__construct($logger, $accountRepository);
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function execute(): ?array
    {
        $this->logger->info("Get all accounts");
        $accounts = $this->getPdoRepository()->findAll();

        if ($accounts === null) {
            throw new ResourceNotFoundException("No accounts");
        }

        $accountsDto  = array_map(
            static fn($account) => new AccountDTO(
                $account
            ),
            $accounts
        );

        return array_map(static fn($dto) => $dto->toArray(), $accountsDto);
    }
}
