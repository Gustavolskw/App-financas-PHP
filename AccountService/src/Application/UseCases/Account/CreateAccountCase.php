<?php

namespace App\Application\UseCases\Account;

use App\Application\Handlers\ServiceHttpHandler;
use App\Application\UseCases\UseCaseService;
use App\Domain\DTO\AccountDTO;
use App\Domain\Entity\Account;
use App\Domain\Exception\InvalidUserException;
use App\Domain\Interfaces\AccountRepository;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Log\LoggerInterface;

class CreateAccountCase extends UseCaseService
{
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly AccountRepository $accountRepository,
        private readonly ServiceHttpHandler $httpService
    ) {
        parent::__construct($logger, $accountRepository);
    }

    /**
     * @throws GuzzleException|InvalidUserException
     */
    public function execute(array $accountData, bool $isNewAccount = false): AccountDTO
    {
        try {
            if (!$isNewAccount) {
                $this->verifyUser($accountData["userId"], $accountData["userEmail"]);
            }
            $this->logger->info("Create account". json_encode($accountData, JSON_THROW_ON_ERROR));
            $newAccount = new Account(
                null,
                $accountData["userId"],
                $accountData["userEmail"] ?? $accountData["email"],
                $accountData["name"] ?? "Conta Corrente Padrão - " . $accountData["userId"],
                $accountData["description"]?? "Conta Corrente Padrão para usuario novo!",
                true,
                null,
                null
            );

            $account = $this->accountRepository->save($newAccount);
        } catch (Exception $exception) {
            if ($exception instanceof InvalidUserException) {
                throw new InvalidUserException($exception->getMessage());
            }
            throw new \RuntimeException($exception->getMessage());
        }
        if ($account === null) {
            throw new \DomainException("Account not created");
        }

        return new AccountDTO($account);
    }


    /**
     * @throws GuzzleException
     * @throws JsonException
     * @throws InvalidUserException
     */
    public function verifyUser(int $userId, string $userEmail): void
    {
        $response = $this->httpService->handleUserValidationRequest("http://nginx/auth/user/verify/$userId", [
            'email' => $userEmail
        ]);
        $this->logger->info("Response from user verify: " . $response);
        if (!$response) {
            throw new InvalidUserException("User not found");
        }
    }
}
