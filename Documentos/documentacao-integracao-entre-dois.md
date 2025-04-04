Entendido! Vou ajustar o documento que você forneceu, incluindo a lógica de reconexão automática para o **Consumer** (Microserviço Conta) e também para o **Producer** (Microserviço Cadastro). A reconexão será implementada nas classes `RabbitMQConsumer` e `RabbitMQProducer` usando `PhpAmqpLib`, garantindo resiliência em caso de falhas no RabbitMQ. Vou manter a estrutura do código original e adicionar apenas as alterações necessárias.

Aqui está o documento revisado com a reconexão automática incluída:

---

### **Microserviço 1: Cadastro (Producer)**

#### **Visão Geral**

- **Função**: API REST para cadastrar usuários e publicar na fila `user_registration`.
- **Endpoints**: `POST /register` (cadastra usuário).

#### **1. Producer (RabbitMQProducer.php)**

Classe ajustada com reconexão automática.

```php
<?php
namespace App\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQProducer
{
    private $connection;
    private $channel;
    private $queue;

    public function __construct(string $queue = 'user_registration')
    {
        $this->queue = $queue;
        $this->connect();
    }

    private function connect(): void
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    private function reconnect(): void
    {
        $this->__destruct();
        echo " [i] Tentando reconectar ao RabbitMQ (Producer)...\n";
        $this->connect();
        echo " [i] Reconexão bem-sucedida (Producer)!\n";
    }

    public function publish(string $message): void
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
                $this->channel->basic_publish($msg, '', $this->queue);
                return; // Publicação bem-sucedida, sai do loop
            } catch (\Exception $e) {
                echo " [x] Erro ao publicar: " . $e->getMessage() . "\n";
                $attempts++;
                if ($attempts < $maxAttempts) {
                    sleep(5); // Aguarda 5 segundos antes de tentar reconectar
                    $this->reconnect();
                } else {
                    throw new \Exception("Falha ao publicar após $maxAttempts tentativas");
                }
            }
        }
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
```

**Mudanças**:

- Adicionado `connect()` e `reconnect()` para gerenciar a conexão.
- O método `publish()` agora tenta reconectar até 3 vezes (`$maxAttempts`) em caso de falha, com intervalo de 5 segundos entre tentativas.

#### **2. Service (UserService.php)**

Sem alterações, pois a reconexão é tratada no `RabbitMQProducer`.

```php
<?php
namespace App\Services;

use App\RabbitMQ\RabbitMQProducer;

class UserService
{
    private $producer;

    public function __construct(RabbitMQProducer $producer)
    {
        $this->producer = $producer;
    }

    public function registerUser(array $userData): array
    {
        $message = json_encode($userData);
        $this->producer->publish($message);
        return ['status' => 'success', 'message' => 'Usuário registrado e enviado para criação de conta'];
    }
}
```

#### **3. Controller (UserController.php)**

Sem alterações.

```php
<?php
namespace App\Controllers;

use App\Services\UserService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class UserController
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function register(Request $request, Response $response): void
    {
        $data = json_decode($request->getContent(), true) ?: [];

        if (empty($data['name']) || empty($data['email'])) {
            $response->status(400);
            $response->end(json_encode(['error' => 'Nome e email são obrigatórios']));
            return;
        }

        $result = $this->userService->registerUser($data);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($result));
    }
}
```

#### **4. Servidor (server.php)**

Sem alterações.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Swoole\Http\Server;
use App\Controllers\UserController;
use App\Services\UserService;
use App\RabbitMQ\RabbitMQProducer;

$server = new Server('0.0.0.0', 9501);

$server->set([
    'worker_num' => 4,
]);

$producer = new RabbitMQProducer();
$service = new UserService($producer);
$controller = new UserController($service);

$server->on('request', function ($request, $response) use ($controller) {
    if ($request->server['request_method'] === 'POST' && $request->server['request_uri'] === '/register') {
        $controller->register($request, $response);
    } else {
        $response->status(404);
        $response->end('Not Found');
    }
});

$server->on('start', function (Server $server) {
    echo "API de Cadastro rodando em http://0.0.0.0:9501\n";
});

$server->start();
```

#### **Teste**

- Execute: `php server.php`.
- POST: `curl -X POST -H "Content-Type: application/json" -d '{"name":"João","email":"joao@example.com"}' http://localhost:9501/register`.
- Se o RabbitMQ estiver fora, verá tentativas de reconexão no terminal.

---

### **Microserviço 2: Conta (Consumer + API REST)**

#### **Visão Geral**

- **Função**:
  - **Consumer**: Consome a fila `user_registration` para criar contas, com reconexão automática.
  - **API REST**: Expõe endpoints como `GET /accounts`.
- **Endpoints**: `GET /accounts` (lista contas criadas).

#### **1. Consumer (RabbitMQConsumer.php)**

Classe ajustada com reconexão automática.

```php
<?php
namespace App\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQConsumer
{
    private $connection;
    private $channel;
    private $queue;

    public function __construct(string $queue = 'user_registration')
    {
        $this->queue = $queue;
        $this->connect();
    }

    private function connect(): void
    {
        $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    private function reconnect(): void
    {
        $this->__destruct();
        echo " [i] Tentando reconectar ao RabbitMQ (Consumer)...\n";
        $this->connect();
        echo " [i] Reconexão bem-sucedida (Consumer)!\n";
    }

    public function consume(callable $callback): void
    {
        while (true) {
            try {
                $this->channel->basic_qos(null, 1, null);
                $this->channel->basic_consume($this->queue, '', false, false, false, false, $callback);

                echo " [*] Consumidor iniciado na fila $this->queue\n";

                while ($this->channel->is_consuming()) {
                    $this->channel->wait();
                }
            } catch (\Exception $e) {
                echo " [x] Erro na conexão: " . $e->getMessage() . "\n";
                sleep(5); // Aguarda 5 segundos antes de tentar reconectar
                $this->reconnect();
            }
        }
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
```

**Mudanças**:

- Adicionado `connect()` e `reconnect()` para gerenciar a conexão.
- O método `consume()` agora usa um loop infinito (`while (true)`) com tratamento de exceções, tentando reconectar a cada 5 segundos em caso de falha.

#### **2. Service (AccountService.php)**

Sem alterações.

```php
<?php
namespace App\Services;

use PhpAmqpLib\Message\AMQPMessage;

class AccountService
{
    private $accounts = []; // Simula um "banco de dados"

    public function createAccount(AMQPMessage $msg): void
    {
        $userData = json_decode($msg->body, true);
        $account = [
            'id' => uniqid(),
            'name' => $userData['name'] ?? 'Desconhecido',
            'email' => $userData['email'] ?? 'N/A',
            'balance' => 0.0,
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->accounts[$account['id']] = $account;
        echo " [x] Conta criada para {$account['name']} (ID: {$account['id']})\n";
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }

    public function getAccounts(): array
    {
        return array_values($this->accounts);
    }
}
```

#### **3. Controller (AccountController.php)**

Sem alterações.

```php
<?php
namespace App\Controllers;

use App\Services\AccountService;
use Swoole\Http\Request;
use Swoole\Http\Response;

class AccountController
{
    private $accountService;

    public function __construct(AccountService $accountService)
    {
        $this->accountService = $accountService;
    }

    public function listAccounts(Request $request, Response $response): void
    {
        $accounts = $this->accountService->getAccounts();
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($accounts));
    }

    public function processMessage($msg): void
    {
        $this->accountService->createAccount($msg);
    }
}
```

#### **4. Servidor (server.php)**

Sem alterações significativas, apenas usa o `RabbitMQConsumer` atualizado.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use Swoole\Http\Server;
use Swoole\Process;
use App\Controllers\AccountController;
use App\Services\AccountService;
use App\RabbitMQ\RabbitMQConsumer;

$server = new Server('0.0.0.0', 9502);

$server->set([
    'worker_num' => 4,
]);

$service = new AccountService();
$controller = new AccountController($service);
$consumer = new RabbitMQConsumer();

$server->on('start', function (Server $server) use ($consumer, $controller) {
    $process = new Process(function () use ($consumer, $controller) {
        $consumer->consume([$controller, 'processMessage']);
    });
    $process->start();
    echo "API de Conta rodando em http://0.0.0.0:9502\n";
});

$server->on('request', function ($request, $response) use ($controller) {
    if ($request->server['request_method'] === 'GET' && $request->server['request_uri'] === '/accounts') {
        $controller->listAccounts($request, $response);
    } else {
        $response->status(404);
        $response->end('Not Found');
    }
});

$server->start();
```

#### **Teste**

1. Execute: `php server.php`.
2. Consuma a fila automaticamente ao enviar um usuário pelo Cadastro.
3. Consulte as contas: `curl http://localhost:9502/accounts`.
4. Pare o RabbitMQ (`sudo systemctl stop rabbitmq-server`) e veja a reconexão funcionando.

---

### **Fluxo Completo**

1. Inicie o RabbitMQ: `sudo systemctl start rabbitmq-server`.
2. Inicie a API Conta: `php server.php` (porta 9502).
3. Inicie a API Cadastro: `php server.php` (porta 9501).
4. Cadastre um usuário: `curl -X POST -d '{"name":"João","email":"joao@example.com"}' http://localhost:9501/register`.
5. Consulte as contas: `curl http://localhost:9502/accounts`.

---

### **Estrutura de Diretórios**

#### **Cadastro**

```
cadastro/
├── src/
│   ├── Controllers/UserController.php
│   ├── Services/UserService.php
│   ├── RabbitMQ/RabbitMQProducer.php
├── vendor/
└── server.php
```

#### **Conta**

```
conta/
├── src/
│   ├── Controllers/AccountController.php
│   ├── Services/AccountService.php
│   ├── RabbitMQ/RabbitMQConsumer.php
├── vendor/
└── server.php
```

---

### **Notas**

- **Reconexão no Producer**: O `RabbitMQProducer` tenta reconectar até 3 vezes antes de falhar completamente, ideal para operações pontuais como publicação de mensagens.
- **Reconexão no Consumer**: O `RabbitMQConsumer` usa um loop infinito para garantir que o consumo continue mesmo após falhas, ideal para operações contínuas.
- **Persistência**: Substitua o array `$accounts` por um banco de dados real.
- **Escalabilidade**: Ajuste `worker_num` no Open Swoole conforme necessário.
- **Segurança**: Adicione autenticação e validações nos endpoints.

---

### **Testando a Reconexão**

- **Producer**: Pare o RabbitMQ, envie um POST para `/register` e veja as tentativas de reconexão no terminal do "Cadastro".
- **Consumer**: Pare o RabbitMQ e observe o terminal do "Conta" tentando reconectar a cada 5 segundos até o RabbitMQ voltar.

Se precisar de mais detalhes ou ajustes na documentação, é só avisar!
