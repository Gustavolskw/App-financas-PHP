Entendido! Vou ajustar o **Serviço de Autenticação** para gerar um token JWT que será enviado ao cliente e reutilizado pelo **Serviço de Caixas** para autenticação e validação direta, sem precisar consultar o Serviço de Autenticação novamente. Isso será possível porque ambos os microserviços compartilharão o mesmo `JWT_SECRET` definido no arquivo `.env`. Além disso, implementarei um mecanismo de bloqueio no Serviço de Caixas para proteger todas as rotas, permitindo acesso apenas com um token JWT válido.

Aqui está a implementação atualizada:

---

### Premissas

1. **JWT_SECRET Compartilhado**: Ambos os serviços usarão o mesmo valor de `JWT_SECRET` no `.env`.
2. **Validação Direta**: O Serviço de Caixas validará o token JWT localmente usando a biblioteca `firebase/php-jwt`.
3. **Bloqueio de Rotas**: Todas as rotas do Serviço de Caixas exigirão um token válido no header `Authorization`.

---

### Serviço de Autenticação (Atualizado)

#### .env (Auth Service)

**Arquivo**: `auth-service/.env`

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=finance
DB_USERNAME=root
DB_PASSWORD=password

JWT_SECRET=super_secret_key_123
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/
```

#### AuthController.php

**Arquivo**: `auth-service/src/Controllers/AuthController.php`
(Apenas ajustando para usar o `JWT_SECRET` do `.env`, sem mudanças significativas na lógica)

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
        $this->pdo = Database::getInstance();
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();
        $this->setupRabbitMQProducer();
    }

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

    private function publishToRabbitMQ(string $event, array $data)
    {
        $message = new AMQPMessage(json_encode([
            'event' => $event,
            'data' => $data,
            'timestamp' => time(),
        ]), ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->rabbitMQChannel->basic_publish($message, '', 'auth.events');
    }

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

_(Nota: O `Database.php` e `Routes/api.php` do Serviço de Autenticação permanecem iguais ao anterior.)_

---

### Serviço de Caixas (Atualizado com Validação JWT e Bloqueio de Rotas)

#### .env (Caixas Service)

**Arquivo**: `caixas-service/.env`

```env
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=finance
DB_USERNAME=root
DB_PASSWORD=password

JWT_SECRET=super_secret_key_123  # Mesmo valor do Auth Service
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_VHOST=/

REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

#### Routes/api.php (Atualizado com Middleware de Autenticação)

**Arquivo**: `caixas-service/src/Routes/api.php`

```php
<?php

use Swoole\Http\Request;
use Swoole\Http\Response;
use App\Controllers\CaixasController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

return function (Request $request, Response $response) {
    // Carrega as variáveis do .env
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();

    // Middleware de autenticação
    $token = str_replace('Bearer ', '', $request->header['authorization'] ?? '');
    try {
        $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
        $request->user_id = $decoded->id; // Adiciona o ID do usuário ao request para uso no controller
    } catch (\Exception $e) {
        $response->status(401);
        $response->header('Content-Type', 'application/json');
        $response->end(json_encode(['error' => 'Token inválido ou expirado']));
        return;
    }

    // Normaliza a URI e o método HTTP
    $uri = rtrim($request->server['request_uri'] ?? '/', '/');
    $method = strtoupper($request->server['request_method'] ?? 'GET');

    // Rotas protegidas do serviço de caixas
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

#### CaixasController.php (Atualizado)

**Arquivo**: `caixas-service/src/Controllers/CaixasController.php`

```php
<?php

namespace App\Controllers;

use Swoole\Http\Request;
use Swoole\Http\Response;
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
        $this->pdo = Database::getInstance();
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->redis = new \Redis();
        $this->redis->connect($_ENV['REDIS_HOST'], $_ENV['REDIS_PORT']);

        $this->setupRabbitMQConsumer();
    }

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

    public function index(Request $request, Response $response)
    {
        $userId = $request->user_id; // Obtido do middleware em Routes/api.php
        $stmt = $this->pdo->prepare('SELECT id, name, balance FROM caixas WHERE user_id = ?');
        $stmt->execute([$userId]);
        $caixas = $stmt->fetchAll();

        $response->header('Content-Type', 'application/json');
        $response->end(json_encode($caixas));
    }

    public function show(Request $request, Response $response, string $id)
    {
        $userId = $request->user_id;
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

    public function store(Request $request, Response $response)
    {
        $userId = $request->user_id;
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

    public function update(Request $request, Response $response, string $id)
    {
        $userId = $request->user_id;
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

    public function destroy(Request $request, Response $response, string $id)
    {
        $userId = $request->user_id;
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
}
```

_(Nota: O `Database.php` do Serviço de Caixas permanece igual ao anterior.)_

---

### Mudanças Implementadas

1. **JWT_SECRET Compartilhado**:

   - Ambos os serviços usam o mesmo `JWT_SECRET` (`super_secret_key_123`) no `.env`. Isso permite que o Serviço de Caixas valide o token gerado pelo Serviço de Autenticação.

2. **Validação no Serviço de Caixas**:

   - O arquivo `Routes/api.php` agora inclui um middleware que valida o token JWT antes de processar qualquer rota.
   - O `user_id` extraído do token é adicionado ao objeto `$request` como `$request->user_id`, substituindo a função `verificarToken()` no controller.

3. **Bloqueio de Rotas**:

   - Todas as rotas do Serviço de Caixas são protegidas. Se o token estiver ausente, inválido ou expirado, o cliente recebe um erro 401 antes mesmo de chegar ao controller.

4. **Remoção de Código Redundante**:
   - A função `verificarToken()` foi removida do `CaixasController.php`, já que a validação agora ocorre no nível das rotas.

---

### Testando a Integração

1. **Inicie o Serviço de Autenticação**:

   ```bash
   cd auth-service
   php public/index.php
   ```

2. **Obtenha um Token JWT**:

   ```bash
   curl -X POST http://127.0.0.1:9501/auth/login \
   -H "Content-Type: application/json" \
   -d '{"email": "joao@example.com", "password": "123456"}'
   ```

   Resposta esperada:

   ```json
   {
     "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     "user": { "id": 1, "name": "João", "email": "joao@example.com" }
   }
   ```

3. **Inicie o Serviço de Caixas**:

   ```bash
   cd caixas-service
   php public/index.php
   ```

4. **Acesse uma Rota Protegida**:
   - Com token válido:
     ```bash
     curl -X GET http://127.0.0.1:9502/caixas \
     -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
     ```
     Resposta esperada: Lista de caixas do usuário.
   - Sem token:
     ```bash
     curl -X GET http://127.0.0.1:9502/caixas
     ```
     Resposta esperada:
     ```json
     { "error": "Token inválido ou expirado" }
     ```

---

### Observações

- **Segurança**: Certifique-se de que o `JWT_SECRET` seja forte e único em produção, e nunca versionado no controle de versão.
- **Escalabilidade**: A validação local do JWT reduz a dependência entre serviços, mas exige que o `JWT_SECRET` seja sincronizado entre eles.
- **Outros Serviços**: Essa abordagem pode ser replicada para os serviços de Transações e Relatórios, aplicando o mesmo middleware nas rotas.

O que achou dessa implementação? Se precisar ajustar algo ou implementar nos outros serviços, é só pedir!
