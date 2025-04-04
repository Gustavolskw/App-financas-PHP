Vou atualizar o **Serviço de Caixas** (contas) para usar a conexão PDO separada em `Database.php` e carregar as credenciais do banco de dados e do RabbitMQ a partir de um arquivo `.env`, seguindo o mesmo padrão que aplicamos ao Serviço de Autenticação. O serviço continuará sendo um consumidor do RabbitMQ (fila `transacao.atualizar_saldo`) e usará o Redis para cache, com todas as configurações vindas do `.env`.

Aqui está a versão atualizada:

---

### Estrutura do Projeto

```
caixas-service/
├── public/
│   └── index.php
├── src/
│   ├── Core/
│   │   ├── Bootstrap.php
│   │   └── Database.php  // Novo arquivo para conexão PDO
│   ├── Controllers/
│   │   └── CaixasController.php
│   └── Routes/
│       └── api.php
├── .env  // Novo arquivo de configuração
├── vendor/
└── composer.json
```

---

### .env

**Arquivo**: `.env`

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=finance
DB_USERNAME=root
DB_PASSWORD=password

JWT_SECRET=secret_key
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

---

### public/index.php

**Arquivo**: `public/index.php`
(Sem alterações)

```php
<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Core\Bootstrap;

Bootstrap::run();
```

---

### src/Core/Bootstrap.php

**Arquivo**: `src/Core/Bootstrap.php`
(Mantido como você enviou, apenas com porta 9502)

```php
<?php

namespace App\Core;

use Swoole\Http\Server;

class Bootstrap {
    public static function run() {
        $server = new Server("0.0.0.0", 9502);

        $server->set([
            'worker_num' => 4,
            'task_worker_num' => 2,
        ]);

        $server->on("task", function ($server, $task_id, $src_worker_id, $data) {
            echo "Task $task_id: " . json_encode($data) . "\n";
            $server->finish("$task_id done");
        });

        $server->on("finish", function ($server, $task_id, $data) {
            echo "Task $task_id finished: $data\n";
        });

        $server->on("start", function (Server $server) {
            echo "Swoole HTTP Server started at http://127.0.0.1:9502\n";
        });

        $server->on("request", function ($request, $response) {
            $routes = require __DIR__ . '/../Routes/api.php';
            $routes($request, $response);
        });

        $server->start();
    }
}
```

---

### src/Core/Database.php

**Arquivo**: `src/Core/Database.php`
(Novo arquivo para conexão PDO, idêntico ao do Serviço de Autenticação)

```php
<?php

namespace App\Core;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s',
            $_ENV['DB_HOST'],
            $_ENV['DB_PORT'],
            $_ENV['DB_DATABASE']
        );

        try {
            $this->pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Erro ao conectar ao banco de dados: ' . $e->getMessage());
        }
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }
}
```

---

### Routes/api.php

**Arquivo**: `src/Routes/api.php`
(Sem alterações)

```php
<?php

use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Controllers\CaixasController;

return function (Request $request, Response $response) {
    $uri = rtrim($request->server['request_uri'] ?? '/', '/');
    $method = strtoupper($request->server['request_method'] ?? 'GET');

    $routes = [
        'GET' => [
            '/caixas' => [CaixasController::class, 'index'],
            '/caixas/{id}' => [CaixasController::class, 'show'],
        ],
        'POST' => [
            '/caixas' => [CaixasController::class, 'store'],
        ],
        'PUT' => [
            '/caixas/{id}' => [CaixasController::class, 'update'],
        ],
        'DELETE' => [
            '/caixas/{id}' => [CaixasController::class, 'destroy'],
        ],
    ];

    $params = [];
    foreach ($routes[$method] ?? [] as $route => $handler) {
        $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        if (preg_match("#^$routePattern$#", $uri, $matches)) {
            if (count($matches) > 1) {
                array_shift($matches);
                $params = $matches;
            }
            [$controllerClass, $methodName] = $handler;
            $controller = new $controllerClass();
            $controller->$methodName($request, $response, ...$params);
            return;
        }
    }

    $response->status(404);
    $response->header('Content-Type', 'application/json');
    $response->end(json_encode(['error' => 'Rota não encontrada']));
};
```

---

### CaixasController.php (Atualizado com PDO Separado e .env)

**Arquivo**: `src/Controllers/CaixasController.php`

```php
<?php

namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Core\Database;
use Dotenv\Dotenv;

class CaixasController
{
    private $pdo;
    private $redis;

    public function __construct()
    {
        // Carrega o PDO do Database.php
        $this->pdo = Database::getInstance();

        // Carrega as variáveis do .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        // Conexão com Redis
        $this->redis = new \Redis();
        $this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);

        // Configuração do RabbitMQ como consumidor
        $this->setupRabbitMQConsumer();
    }

    /**
     * Configura o consumidor do RabbitMQ para atualizar saldos
     */
    private function setupRabbitMQConsumer()
    {
        $connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASSWORD'],
            $_ENV['RABBITMQ_VHOST']
        );
        $channel = $connection->channel();
        $channel->queue_declare('transacao.atualizar_saldo', false, true, false, false);

        $channel->basic_consume(
            'transacao.atualizar_saldo',
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $msg) {
                $data = json_decode($msg->body, true);
                $caixaId = $data['caixa_id'];
                $valor = $data['valor'];
                $userId = $data['user_id'] ?? null;

                $stmt = $this->pdo->prepare('UPDATE caixas SET balance = balance + ?, updated_at = NOW() WHERE id = ?');
                $stmt->execute([$valor, $caixaId]);

                if ($userId) {
                    $balance = $this->pdo->query("SELECT balance FROM caixas WHERE id = $caixaId")->fetchColumn();
                    $this->redis->set("caixa:user:{$userId}:{$caixaId}", $balance, 600);
                }

                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        );

        \Swoole\Coroutine::create(function () use ($channel) {
            while ($channel->is_consuming()) {
                $channel->wait();
            }
        });
    }

    /**
     * GET /caixas
     */
    public function index(Request $request, Response $response)
    {
        $userId = $this->verificarToken($request);
        $stmt = $this->pdo->prepare('SELECT id, name, balance FROM caixas WHERE user_id = ?');
        $stmt->execute([$userId]);
        $caixas = $stmt->fetchAll();

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($caixas));
    }

    /**
     * GET /caixas/{id}
     */
    public function show(Request $request, Response $response, string $id)
    {
        $userId = $this->verificarToken($request);
        $stmt = $this->pdo->prepare('SELECT id, name, balance FROM caixas WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $caixa = $stmt->fetch();

        if (!$caixa) {
            $response->status(404);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Caixa não encontrado']));
            return;
        }

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($caixa));
    }

    /**
     * POST /caixas
     */
    public function store(Request $request, Response $response)
    {
        $userId = $this->verificarToken($request);
        $data = json_decode($request->rawContent(), true) ?? [];
        $name = $data['name'] ?? null;

        if (!$name) {
            $response->status(400);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Nome do caixa é obrigatório']));
            return;
        }

        $stmt = $this->pdo->prepare('INSERT INTO caixas (user_id, name, balance, created_at) VALUES (?, ?, 0.00, NOW())');
        $stmt->execute([$userId, $name]);
        $id = $this->pdo->lastInsertId();

        $this->redis->set("caixa:user:{$userId}:{$id}", 0.00, 600);

        $response->status(201);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['id' => $id, 'name' => $name, 'balance' => 0.00]));
    }

    /**
     * PUT /caixas/{id}
     */
    public function update(Request $request, Response $response, string $id)
    {
        $userId = $this->verificarToken($request);
        $data = json_decode($request->rawContent(), true) ?? [];
        $name = $data['name'] ?? null;

        if (!$name) {
            $response->status(400);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Nome do caixa é obrigatório']));
            return;
        }

        $stmt = $this->pdo->prepare('UPDATE caixas SET name = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
        $stmt->execute([$name, $id, $userId]);

        if ($stmt->rowCount() === 0) {
            $response->status(404);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Caixa não encontrado']));
            return;
        }

        $stmt = $this->pdo->prepare('SELECT id, name, balance FROM caixas WHERE id = ?');
        $stmt->execute([$id]);
        $caixa = $stmt->fetch();

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($caixa));
    }

    /**
     * DELETE /caixas/{id}
     */
    public function destroy(Request $request, Response $response, string $id)
    {
        $userId = $this->verificarToken($request);
        $stmt = $this->pdo->prepare('SELECT balance FROM caixas WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $balance = $stmt->fetchColumn();

        if ($balance === false) {
            $response->status(404);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Caixa não encontrado']));
            return;
        }

        if ($balance != 0) {
            $response->status(400);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Caixa com saldo não pode ser deletado']));
            return;
        }

        $stmt = $this->pdo->prepare('DELETE FROM caixas WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $this->redis->del("caixa:user:{$userId}:{$id}");

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['message' => 'Caixa deletado']));
    }

    /**
     * Verifica e retorna o ID do usuário a partir do token JWT
     */
    private function verificarToken(Request $request): int
    {
        $token = str_replace('Bearer ', '', $request->header['authorization'] ?? '');
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            return $decoded->id;
        } catch (\Exception $e) {
            throw new \Exception('Token inválido ou expirado', 401);
        }
    }
}
```

---

### Dependências Necessárias

```bash
composer require firebase/php-jwt
composer require php-amqplib/php-amqplib
composer require vlucas/phpdotenv
```

---

### Banco de Dados (MySQL)

A tabela `caixas` permanece a mesma:

```sql
CREATE TABLE caixas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

---

### Testando

1. **Inicie o servidor**:

   ```bash
   php public/index.php
   ```

   O serviço estará em `http://127.0.0.1:9502`.

2. **Consumo do RabbitMQ**:

   - O consumidor da fila `transacao.atualizar_saldo` é configurado no construtor e roda em uma co-rotina do OpenSwoole.

3. **Exemplos de Requisições**:
   - **POST /caixas**:
     ```bash
     curl -X POST http://127.0.0.1:9502/caixas \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer {token}" \
     -d '{"name": "Caixa Corrente"}'
     ```
   - **GET /caixas**:
     ```bash
     curl -X GET http://127.0.0.1:9502/caixas \
     -H "Authorization: Bearer {token}"
     ```

---

### Melhorias Implementadas

1. **Separação do PDO**:

   - A conexão com o MySQL foi movida para `Database.php`, usando o padrão Singleton.

2. **Uso do .env**:

   - Credenciais do MySQL, RabbitMQ, Redis e JWT são lidas do `.env` com `$_ENV`.
   - O carregamento do `.env` ocorre tanto no `Database.php` quanto no `CaixasController.php`.

3. **Segurança**:
   - Configurações sensíveis estão fora do código, no `.env`.
   - O PDO usa boas práticas como `ERRMODE_EXCEPTION`.

---

### Observações

- **Redis**: A conexão agora usa variáveis do `.env` (`REDIS_HOST` e `REDIS_PORT`).
- **RabbitMQ**: O consumidor foi ajustado para usar as credenciais do `.env`.
- **Exceções**: O método `verificarToken()` lança exceções que devem ser tratadas nos endpoints para retornar 401 adequadamente.

Se precisar de mais ajustes ou aplicar isso a outros serviços, é só avisar! O que achou?
