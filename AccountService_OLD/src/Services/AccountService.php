<?php
namespace Acc\Services;

use Acc\Config\Database;
use Acc\DTO\AccountDTO;
use Acc\Entity\Account;
use Acc\Http\ExternalConsumer;
use Acc\JWT\UtilJwt;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;


class AccountService
{
    private $rabbitMQProducer;

    private $rabbitMQDirectQueue;

    private $rabbitMQFanOutExge;

    private $redisService;

    private $utilJwt;

    private $apiConsumer;

    public function __construct()
    {
        Database::bootEloquent();
        $this->utilJwt = new UtilJwt();
        $this->apiConsumer = new ExternalConsumer();

    }

    public function createAccount(?int $userId, string $email, string $name, string $description, string $token): array
    {
        $decoded = $this->utilJwt->decodeJwt($token);

        var_dump($decoded);

        if ($userId != null) {
            try {
                $data = $this->apiConsumer->consumeUserValidaty($userId, $email);
            } catch (RequestException $e) {
                return self::generateResponse("Erro ao validar usuario" . $e->getMessage(), 400);
            }
            if (!$data) {
                return self::generateResponse("Usuário invalido ou não encontrado.", 404);
            }
            $newAccount = Account::create([
                'userId' => $userId,
                'userEmail' => $email,
                'name' => $name,
                'description' => $description,
                'status' => true,
            ]);
        } else {
            $newAccount = Account::create([
                'userId' => $decoded->sub,
                'userEmail' => $decoded->email,
                'name' => $name,
                'description' => $description,
                'status' => true,
            ]);
        }

        $newAccount->save();


        $accountResponse = AccountDTO::fromArray($newAccount->toArray())->toArray();

        return self::generateResponse("Conta aberta com sucesso!", 200, $accountResponse);
    }

    public function createNewUserAccount(int $userId, string $email)
    {
        try {
            $newAccount = Account::create([
                'userId' => $userId,
                'userEmail' => $email,
                'name' => "Conta Principal",
                'description' => "Conta Principal do Usuario!",
                'status' => true,
            ]);

            $newAccount->save();
        } catch (Exception $e) {
            error_log($e);
        }

    }

    public function inactivateUserAccounts(int $userId)
    {
        try {
            Account::where('userId', '=', $userId)
                ->update(['status' => false]);
        } catch (Exception $e) {
            error_log($e);
        }
    }
    public function reactivateUserAccounts(int $userId)
    {
        try {
            Account::where('userId', '=', $userId)
                ->update(['status' => true]);
        } catch (Exception $e) {
            error_log($e);
        }
    }

    public function getAllAccounts()
    {
        $accocunts = Account::all()->toArray();
        $accountDTOs = array_map(fn($account) => AccountDTO::fromArray($account), $accocunts);

        return $this->generateResponse("Lista de Contas", 200, array_map(fn($dto) => $dto->toArray(), $accountDTOs));

    }
    public function getAccountById(int $accId)
    {
        $account = Account::where('id', '=', $accId)->first();
        $accountDto = AccountDTO::fromArray($account->toArray())->toArray();
        return self::generateResponse("Usuario encontrado com sucesso!", 200, $accountDto);
    }

    public function getUserAccounts(int $userId)
    {
        $accounts = Account::where('userId', '=', $userId)->toArray();
        $accountDTOs = array_map(fn($account) => AccountDTO::fromArray($account), $accounts);
        return self::generateResponse("Usuario encontrado com sucesso!", 200, $accountDTOs);
    }

    public function updateAccount(int $accId, ?string $accName, ?string $accDescription)
    {
        $account = Account::where('id', '=', $accId)->first();

        if (boolval($account->status) == false) {
            self::generateResponse("Erro ao alterar conta, a mesma esta inativada!", 422);
        }
        $account->name = $accName ?? $account->name;
        $account->description = $accDescription ?? $account->description;

        $accountDto = AccountDTO::fromArray($account->toArray())->toArray();
        return self::generateResponse("Usuario encontrado com sucesso!", 200, $accountDto);
    }

    public function reactivateAccount(int $id)
    {
        $account = Account::where('id', '=', $id)->firstOrFail();
        try {
            $data = $this->apiConsumer->consumeUserValidaty($account->userId, $account->userEmail);
        } catch (RequestException $e) {
            return self::generateResponse("Erro ao validar usuario" . $e->getMessage(), 400);
        }
        if ($data) {
            $account->status = true;
            self::generateResponse("Conta Reativada com sucesso!", 200);
        } else {
            self::generateResponse("Erro ao reativar a conta do usuario pois o mesmo esta inativado!", 422);
        }
    }

    public function inactivateAccount(int $accountId)
    {
        Account::where('id', '=', $accountId)
            ->update(['status' => false]);
        return self::generateResponse("Conta Desativada com sucesso!", 200);
    }

    private function generateResponse(string $message, int $status, ?array $data = null, ?string $token = null): array
    {
        $response = [
            'message' => $message,
            'status' => $status,
        ];
        if (isset($data)) {
            $response['data'] = $data;
        }
        if (isset($token)) {
            $response['token'] = $token;
        }

        return $response;
    }
}