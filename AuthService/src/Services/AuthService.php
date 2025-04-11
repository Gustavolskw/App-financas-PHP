<?php
namespace Auth\Services;

use Auth\DTO\UserDTO;
use Auth\Entity\User;
use Auth\JWT\UtilJwt;
use Auth\Message\DirectQueueProducer;
use Auth\Message\FanoutExchangeProducer;
use Auth\Services\RedisService;
use Auth\Model\UserModel;

class AuthService
{

    private $rabbitMQDirectQueue;

    private $rabbitMQFanOutExge;

    private $redisService;

    private $utilJwt;

    private $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->redisService = new RedisService();
        $this->utilJwt = new UtilJwt();
        $this->rabbitMQDirectQueue = new DirectQueueProducer();
        $this->rabbitMQFanOutExge = new FanoutExchangeProducer();
    }

    public function loginUser(string $email, string $password): array
    {

        $userOnBase = $this->userModel->findByEmail($email);
        if (!$userOnBase) {
            return $this->generateResponse('User or Password invalid!', 401);
        }


        if (!boolval($userOnBase->getStatus())) {
            return $this->generateResponse("Usuario Invalido!", 422);
        }


        if (!password_verify($password, $userOnBase->getPassword())) {
            return $this->generateResponse('User or Password invalid!', 401);
        }

        $oldTokenKey = "user:{$userOnBase->getId()}:token";

        $this->redisService->porcessAndDelteTokenByUserId($oldTokenKey, $userOnBase->getId());

        $payload = $this->utilJwt->buildPayload($userOnBase);

        $token = $this->utilJwt->encodeJwt($payload);


        $this->redisService->setToken($token, 3600, $userOnBase);

        return $this->generateResponse('Logged in successfully', 200, null, $token);

    }

    public function register(string $name, string $email, string $password, int $role = User::ROLE_USER): array
    {
        if ($this->userModel->userExistsByEmail($email)) {
            return $this->generateResponse('Email Invalido ou indisponivel!', 422);
        }
        if (!in_array($role, [User::ROLE_USER, User::ROLE_ADMIN])) {
            return $this->generateResponse('Role inválido', 422);
        }

        $newUser = new User(null, $name, $email, $password, $role, true);
        var_dump($newUser);
        $user = $this->userModel->create($newUser);
        var_dump($user);
        $payload = $this->utilJwt->buildPayload($user);
        $token = $this->utilJwt->encodeJwt($payload);

        $this->redisService->setToken($token, 3600, $user);



        $userResponse = UserDTO::fromArray($user)->toArray();

        $this->rabbitMQDirectQueue->publish($_ENV['RABBITMQ_QUEUE'], ['userId' => $user->getId(), 'email' => $user->getEmail()]);

        $this->cleanUserCache();

        return $this->generateResponse('User registered successfully', 201, $userResponse, $token);
    }

    public function getUserByEmail(string $email): array
    {
        $user = $this->userModel->findByEmail($email);
        if ($user == null) {
            return $this->generateResponse('Usuario nao encontrado!', 404);
        }
        //return $user->toArray();
        return $this->generateResponse('Usuario encontrado!', 200, UserDTO::fromArray($user)->toArray());
    }

    public function getAllUsers(): array
    {

        $usersCached = $this->redisService->getCachedData("userCache", "users");
        if (!$usersCached) {
            $users = $this->userModel->findAll();
            if ($users == null) {
                $this->generateResponse("Sem usuarios nos registros!", 200);
            }
            $this->setUserCache($users);
            $userDTOs = array_map(fn($user) => UserDTO::fromArray($user), $users);

            return $this->generateResponse("Lista de Usuarios", 200, array_map(fn($dto) => $dto->toArray(), $userDTOs));
        }
        if ($usersCached == null) {
            $this->generateResponse("Sem usuarios nos registros!", 200);
        }

        $users = json_decode($usersCached, true);

        $userDTOs = array_map(fn($user) => UserDTO::fromArray($user), $users);

        return $this->generateResponse("Lista de Usuarios", 200, array_map(fn($dto) => $dto->toArray(), $userDTOs));
    }

    public function updateUser(string $token, int $id, ?string $name, ?string $email, ?string $password, ?int $role, bool $status): array
    {

        if (isset($role) && !in_array($role, [User::ROLE_USER, User::ROLE_ADMIN])) {
            return $this->generateResponse("Cargo invalido!", 422);
        }
        $decoded = $this->utilJwt->decodeJwt($token);

        $userId = $decoded->sub;
        $userEmail = $decoded->email;

        $storedUserId = $this->redisService->getDataByToken($token);
        if (!$storedUserId || $storedUserId != $userId) {
            return $this->generateResponse('Invalid or missing token', 401);
        }

        if ($email && $this->userModel->userExistsByEmail($email) && $email !== $userEmail) {
            return $this->generateResponse("Email already in use", 422);
        }

        if ($decoded->role >= User::ROLE_ADMIN) {
            $user = $this->userModel->findById($id);
        } elseif ($userId == $id) {
            $user = $this->userModel->findById($userId);
        } else {
            return $this->generateResponse("Action not allowed", 403);
        }
        if ($status == null) {
            if (!$user || boolval($user->getStatus()) === false) {
                return $this->generateResponse("Usuario Inativado, reative-o para poder Atualizar!", 422);
            }
        }

        $updateUser = new User(null, $name, $email, $password, $role, $status, null, null);

        if (isset($status) && boolval($user->getStatus())) {
            $this->rabbitMQFanOutExge->publish($_ENV['RABBITMQ_FAN_OUT_EXCHANGE_REACT'], ['userId' => $user->getId()]);
        }

        $this->cleanUserCache();
        if ($userId == $id) {
            $this->redisService->removeToken($token);

            $payload = $this->utilJwt->buildPayload($user);

            $tokenNew = $this->utilJwt->encodeJwt($payload);

            $this->redisService->setToken($token, 3600, $user);

            return $this->generateResponse('User updated successfully', 200, UserDTO::fromArray($user)->toArray(), $tokenNew);
        }
        return $this->generateResponse('User updated successfully', 200, UserDTO::fromArray($user)->toArray());

    }


    public function removeUser(int $id, string $token)
    {

        $decoded = $this->utilJwt->decodeJwt($token);
        $decodedId = $decoded->sub;
        $decodedRole = $decoded->role;

        if ($decodedId == $id) {
            $userFound = $this->userModel->findById($decodedId);
            $message = "Funcoes desativadas, mas accesso mantido ate a validade da sua sessao";
        } else if ($decodedRole == User::ROLE_ADMIN && $decodedId != $id) {
            $userFound = $this->userModel->findById($id);
            $message = "Funcoes desativadas, mas accesso mantido ate a validade da sessao do usuario";
        } else {
            return $this->generateResponse("Action not allowed", 403);
        }

        if ($userFound->getStatus() == false) {
            return $this->generateResponse("Usuario ja Inativado!", 200);
        }

        $this->userModel->remove($userFound->getId());

        $this->rabbitMQFanOutExge->publish($_ENV['RABBITMQ_FAN_OUT_EXCHANGE_INACT'], ['userId' => $userFound->getId()]);

        $this->cleanUserCache();
        return $this->generateResponse("Usuario Inativado com Sucesso!", 200, ["message" => $message]);
    }


    public function verifyUser(int $id, string $email): bool
    {
        $user = $this->userModel->findByIdAndEmail($id, $email);
        if ($user == null) {
            return false;
        } else if (boolval($user->getStatus()) == false) {
            return false;
        }
        return true;
    }

    private function setUserCache(array $data)
    {
        $this->redisService->setDataOnCache("userCache", "users", $data);
    }

    private function cleanUserCache()
    {
        $this->redisService->dropCachedData("userCache", "users");
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