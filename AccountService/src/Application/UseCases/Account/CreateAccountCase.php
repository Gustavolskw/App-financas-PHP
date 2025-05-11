<?php

namespace App\Application\UseCases\Account;

use App\Application\UseCases\UseCaseService;
use App\Domain\DTO\AccountDTO;
use App\Domain\Entity\Account;
use App\Domain\Exception\InvalidUserException;
use App\Domain\Interfaces\AccountRepository;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Log\LoggerInterface;

class CreateAccountCase extends UseCaseService
{
    public function __construct(
        private readonly LoggerInterface   $logger,
        private readonly AccountRepository $accountRepository
    ) {
        parent::__construct($logger, $accountRepository);
    }

    /**
     * @throws GuzzleException|InvalidUserException
     */
    public function execute(array $accountData, bool $isNewAccount = false ): AccountDTO
    {
        try{
            if(!$isNewAccount){
                $this->verifyUser($accountData["userId"], $accountData["userEmail"]);
            }
            $this->logger->info("Create account". json_encode($accountData, JSON_THROW_ON_ERROR));
            $newAccount = new Account(null,
                $accountData["userId"],
                $accountData["userEmail"] ?? $accountData["email"],
                $accountData["name"] ?? "Conta Corrente Padrão - " . $accountData["userId"],
                $accountData["description"]?? "Conta Corrente Padrão para usuario novo!",
                true,
                null,
                null );

            $account = $this->accountRepository->save($newAccount);

        }catch (Exception $exception){
            if($exception instanceof InvalidUserException)
            {
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
     * @throws InvalidUserException
     * @throws JsonException
     */
    public function verifyUser(int $userId, string $userEmail): void
    {
        $client = new Client();
        $response = $client->request("GET", "http://nginx/auth/user/verify/$userId", [
            'query' => ['email' => $userEmail],
            'timeout' => 5.0,
        ]);
        $json = (string) $response->getBody();
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if(!$data){
            throw new InvalidUserException("Usuario inválido");
        }

    }

}