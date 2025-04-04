Vou melhorar a conexão com o MySQL separando o PDO em um arquivo dedicado (`Database.php`), e farei com que as credenciais sejam lidas de um arquivo `.env` usando a biblioteca `vlucas/phpdotenv`. Isso segue boas práticas de separação de responsabilidades e segurança. Vou atualizar apenas o **Serviço de Autenticação** com o producer do RabbitMQ, mas a lógica pode ser aplicada aos outros serviços.

Aqui está a versão atualizada:

---

### Estrutura do Projeto

```
auth-service/
├── public/
│   └── index.php
├── src/
│   ├── Core/
│   │   ├── Bootstrap.php
│   │   └── Database.php  // Novo arquivo para conexão PDO
│   ├── Controllers/
│   │   └── AuthController.php
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
```

---

### Instalação do phpdotenv

Instale a biblioteca para carregar o `.env`:

```bash
composer require vlucas/phpdotenv
```

---

### public/index.php

**Arquivo**: `public/index.php`
(Sem alterações, apenas carregando o autoload e o Bootstrap)

```php
<?php
require __DIR__ . '/../vendor/autoload.php';
use App\Core\Bootstrap;

Bootstrap::run();
```

---

### src/Core/Bootstrap.php

**Arquivo**: `src/Core/Bootstrap.php`
(Mantido como você enviou, sem alterações)

```php
<?php

namespace App\Core;

use Swoole\Http\Server;

class Bootstrap {
    public static function run() {
        $server = new Server("0.0.0.0", 9501);

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
            echo "Swoole HTTP Server started at http://127.0.0.1:9501\n";
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
(Novo arquivo para gerenciar a conexão PDO)

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
        // Carrega as variáveis do .env
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

    /**
     * Retorna a instância única do PDO (Singleton)
     */
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
(Sem alterações, apenas para referência)

```php
<?php

use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Controllers\AuthController;

return function (Request $request, Response $response) {
    $uri = rtrim($request->server['request_uri'] ?? '/', '/');
    $method = strtoupper($request->server['request_method'] ?? 'GET');

    $routes = [
        'GET' => [
            '/auth/me' => [AuthController::class, 'me'],
        ],
        'POST' => [
            '/auth/login' => [AuthController::class, 'login'],
            '/auth/register' => [AuthController::class, 'register'],
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

### AuthController.php (Atualizado com PDO Separado e .env)

**Arquivo**: `src/Controllers/AuthController.php`

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

class AuthController
{
    private $pdo;
    private $rabbitMQChannel;

    public function __construct()
    {
        // Carrega o PDO do Database.php
        $this->pdo = Database::getInstance();

        // Carrega as variáveis do .env
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        // Configuração do RabbitMQ como producer
        $this->setupRabbitMQProducer();
    }

    /**
     * Configura o producer do RabbitMQ
     */
    private function setupRabbitMQProducer()
    {
        $connection = new AMQPStreamConnection(
            $_ENV['RABBITMQ_HOST'],
            $_ENV['RABBITMQ_PORT'],
            $_ENV['RABBITMQ_USER'],
            $_ENV['RABBITMQ_PASSWORD'],
            $_ENV['RABBITMQ_VHOST']
        );
        $this->rabbitMQChannel = $connection->channel();
        $this->rabbitMQChannel->queue_declare('auth.events', false, true, false, false);
    }

    /**
     * Publica uma mensagem no RabbitMQ
     */
    private function publishToRabbitMQ(string $event, array $data)
    {
        $message = new AMQPMessage(json_encode([
            'event' => $event,
            'data' => $data,
            'timestamp' => time(),
        ]), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        $this->rabbitMQChannel->basic_publish($message, '', 'auth.events');
    }

    /**
     * POST /auth/login
     */
    public function login(Request $request, Response $response)
    {
        $data = json_decode($request->rawContent(), true) ?? [];
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$email || !$password) {
            $response->status(400);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Email e senha são obrigatórios']));
            return;
        }

        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $payload = [
                'id' => $user['id'],
                'iat' => time(),
                'exp' => time() + 3600,
            ];
            $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

            $this->publishToRabbitMQ('user.logged_in', [
                'user_id' => $user['id'],
                'email' => $user['email'],
            ]);

            $response->header('Content-Type', 'application/json');
            $response->end(json_encode([
                'token' => $token,
                'user' => [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ],
            ]));
        } else {
            $response->status(401);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Credenciais inválidas']));
        }
    }

    /**
     * POST /auth/register
     */
    public function register(Request $request, Response $response)
    {
        $data = json_decode($request->rawContent(), true) ?? [];
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!$name || !$email || !$password) {
            $response->status(400);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Nome, email e senha são obrigatórios']));
            return;
        }

        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $response->status(400);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Email já registrado']));
            return;
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare('INSERT INTO users (name, email, password, created_at) VALUES (?, ?, ?, NOW())');
        $stmt->execute([$name, $email, $hashedPassword]);
        $id = $this->pdo->lastInsertId();

        $payload = [
            'id' => $id,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $token = JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

        $this->publishToRabbitMQ('user.registered', [
            'user_id' => $id,
            'name' => $name,
            'email' => $email,
        ]);

        $response->status(201);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode([
            'user' => ['id' => $id, 'name' => $name, 'email' => $email],
            'token' => $token,
        ]));
    }

    /**
     * GET /auth/me
     */
    public function me(Request $request, Response $response)
    {
        $token = str_replace('Bearer ', '', $request->header['authorization'] ?? '');

        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $stmt = $this->pdo->prepare('SELECT id, name, email FROM users WHERE id = ?');
            $stmt->execute([$decoded->id]);
            $user = $stmt->fetch();

            if (!$user) {
                throw new \Exception('Usuário não encontrado');
            }

            $response->header('Content-Type', 'application/json');
            $response->end(json_encode($user));
        } catch (\Exception $e) {
            $response->status(401);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Token inválido ou expirado']));
        }
    }
}
```

---

### Melhorias Implementadas

1. **Separação do PDO**:

   - A conexão com o MySQL foi movida para `Database.php`, que usa o padrão Singleton para garantir uma única instância do PDO por processo OpenSwoole. Isso melhora a eficiência e evita múltiplas conexões desnecessárias.

2. **Uso do .env**:

   - As credenciais do banco (host, port, database, username, password) e do RabbitMQ (host, port, user, password, vhost) são lidas do `.env` usando `$_ENV`.
   - A chave secreta do JWT também foi movida para o `.env` (`JWT_SECRET`).

3. **Segurança e Configuração**:
   - O carregamento do `.env` é feito tanto no `Database.php` quanto no `AuthController.php` para garantir que todas as dependências tenham acesso às variáveis de ambiente.
   - O PDO agora usa opções recomendadas como `ERRMODE_EXCEPTION` e desativa emulação de prepared statements para maior segurança.

---

### Dependências Necessárias

```bash
composer require firebase/php-jwt
composer require php-amqplib/php-amqplib
composer require vlucas/phpdotenv
```

---

### Testando

1. **Inicie o servidor**:

   ```bash
   php public/index.php
   ```

   O serviço estará em `http://127.0.0.1:9501`.

2. **Exemplos de Requisições**:
   - **POST /auth/register**:
     ```bash
     curl -X POST http://127.0.0.1:9501/auth/register \
     -H "Content-Type: application/json" \
     -d '{"name": "João", "email": "joao@example.com", "password": "123456"}'
     ```
   - **POST /auth/login**:
     ```bash
     curl -X POST http://127.0.0.1:9501/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email": "joao@example.com", "password": "123456"}'
     ```
   - **GET /auth/me**:
     ```bash
     curl -X GET http://127.0.0.1:9501/auth/me \
     -H "Authorization: Bearer {token}"
     ```

---

### Observações

- **Singleton**: O uso do Singleton no `Database.php` é seguro no contexto do OpenSwoole, já que cada worker mantém sua própria instância. Se precisar de uma abordagem diferente (ex.: pool de conexões), podemos ajustar.
- **RabbitMQ**: A publicação continua síncrona. Para assincronia, podemos usar tasks do OpenSwoole se necessário.
- **.env**: Certifique-se de que o arquivo `.env` está no diretório raiz e não versionado no controle de versão (adicione ao `.gitignore`).

Se precisar aplicar isso aos outros serviços ou ajustar algo, é só avisar! O que achou dessa melhoria?
