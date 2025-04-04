Aqui está a documentação unificada em formato Markdown, abrangendo o processo de envio de mensagens (produtor) no serviço de Autenticação e Registros e o consumo automatizado de filas no serviço de Contas Bancárias, com o ajuste para iniciar automaticamente com o Laravel.

---

# Documentação: Integração de Microserviços com RabbitMQ, Laravel e php-amqplib

Esta documentação descreve a implementação de uma arquitetura de microserviços usando Laravel e RabbitMQ como sistema de mensageria. O fluxo consiste em:

- **Serviço de Autenticação e Registros**: Uma API RESTful que cadastra usuários e publica mensagens na fila do RabbitMQ.
- **Serviço de Contas Bancárias**: Um consumidor que escuta automaticamente a fila do RabbitMQ e cria contas bancárias para novos usuários assim que o Laravel é inicializado.

A biblioteca `php-amqplib` é usada para interação com o RabbitMQ, e o consumo da fila é automatizado sem necessidade de intervenção manual após o boot do aplicativo.

---

## Pré-requisitos

- **RabbitMQ**: Instalado e rodando (ex.: `localhost:5672`).
- **Laravel**: Dois projetos Laravel configurados (um para cada serviço).
- **Composer**: Para instalar dependências.
- **PHP**: Versão compatível com Laravel (ex.: 8.x ou superior).

---

## Configuração Inicial

### Instalação da Biblioteca

Em ambos os projetos Laravel, instale a biblioteca `php-amqplib`:

```bash
composer require php-amqplib/php-amqplib
```

### Configuração do .env

Adicione as credenciais do RabbitMQ no arquivo `.env` de ambos os projetos:

```env
RABBITMQ_HOST=localhost
RABBITMQ_PORT=5672
RABBITMQ_USER=guest
RABBITMQ_PASSWORD=guest
RABBITMQ_QUEUE=user_registration
```

---

## Serviço de Autenticação e Registros (Produtor)

### Estrutura

- **Objetivo**: Cadastrar usuários via API RESTful e enviar mensagens ao RabbitMQ.
- **Componentes**:
  - Modelo `User` e migration.
  - Serviço `RabbitMQService` para publicar mensagens.
  - Controlador `UserController` para processar requisições.
  - Rota API para cadastro.

#### 1. Modelo e Migration

Crie o modelo `User` com migration:

```bash
php artisan make:model User -m
```

Em `database/migrations/...create_users_table.php`:

```php
public function up()
{
    Schema::create('users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });
}
```

Execute a migration:

```bash
php artisan migrate
```

#### 2. Serviço RabbitMQ

Crie `app/Services/RabbitMQService.php`:

```php
<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Facades\Log;

class RabbitMQService
{
    protected $connection;
    protected $channel;
    protected $queue;

    public function __construct()
    {
        $this->queue = env('RABBITMQ_QUEUE', 'user_registration');
        $this->connect();
        $this->channel->queue_declare($this->queue, false, true, false, false);
    }

    protected function connect()
    {
        try {
            $this->connection = new AMQPStreamConnection(
                env('RABBITMQ_HOST', 'localhost'),
                env('RABBITMQ_PORT', 5672),
                env('RABBITMQ_USER', 'guest'),
                env('RABBITMQ_PASSWORD', 'guest')
            );
            $this->channel = $this->connection->channel();
            Log::info("Conexão com RabbitMQ estabelecida com sucesso.");
        } catch (\Exception $e) {
            Log::error("Falha ao conectar ao RabbitMQ: " . $e->getMessage());
            throw $e;
        }
    }

    public function publish($message)
    {
        $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
        $this->channel->basic_publish($msg, '', $this->queue);
        Log::info("Mensagem publicada na fila: " . $this->queue);
    }

    // Método consume será usado apenas no serviço de Contas Bancárias
    public function consume($callback)
    {
        while (true) {
            try {
                $this->channel->basic_consume($this->queue, '', false, true, false, false, $callback);
                while ($this->channel->is_consuming()) {
                    $this->channel->wait();
                }
            } catch (\Exception $e) {
                Log::error("Erro no consumo do RabbitMQ: " . $e->getMessage());
                $this->channel->close();
                $this->connection->close();
                $this->connect();
                $this->channel->queue_declare($this->queue, false, true, false, false);
                sleep(5);
            }
        }
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }
}
```

#### 3. Controlador da API

Crie `app/Http/Controllers/Api/UserController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RabbitMQService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Publicar mensagem no RabbitMQ
        $rabbitMQ = new RabbitMQService();
        $message = json_encode([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
        $rabbitMQ->publish($message);

        return response()->json(['message' => 'Usuário cadastrado com sucesso!', 'user' => $user], 201);
    }
}
```

#### 4. Rota da API

Em `routes/api.php`:

```php
use App\Http\Controllers\Api\UserController;

Route::post('/register', [UserController::class, 'register']);
```

---

## Serviço de Contas Bancárias (Consumidor Automatizado)

### Estrutura

- **Objetivo**: Consumir mensagens da fila automaticamente ao iniciar o Laravel e criar contas bancárias.
- **Componentes**:
  - Modelo `BankAccount` e migration.
  - Serviço `RabbitMQService` (reutilizado com método `consume`).
  - Comando `ConsumeUserRegistrations` para processar mensagens.
  - `ServiceProvider` para iniciar o consumidor automaticamente.

#### 1. Modelo e Migration

Crie o modelo `BankAccount`:

```bash
php artisan make:model BankAccount -m
```

Em `database/migrations/...create_bank_accounts_table.php`:

```php
public function up()
{
    Schema::create('bank_accounts', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('account_number')->unique();
        $table->decimal('balance', 10, 2)->default(0.00);
        $table->timestamps();
    });
}
```

Execute a migration:

```bash
php artisan migrate
```

#### 2. Serviço RabbitMQ

Reutilize o mesmo `RabbitMQService` do serviço de Autenticação (já mostrado acima), com o método `consume` configurado para reconexão automática.

#### 3. Comando Consumidor

Crie `app/Console/Commands/ConsumeUserRegistrations.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\RabbitMQService;
use App\Models\BankAccount;
use Illuminate\Support\Facades\Log;

class ConsumeUserRegistrations extends Command
{
    protected $signature = 'rabbitmq:consume-users';
    protected $description = 'Consome mensagens de registro de usuários do RabbitMQ automaticamente';

    public function handle()
    {
        Log::info("Iniciando escuta na fila de registros de usuários...");

        $rabbitMQ = new RabbitMQService();

        // Callback para processar mensagens
        $callback = function ($msg) {
            $data = json_decode($msg->body, true);

            // Criar conta bancária para o usuário
            $accountNumber = 'ACC' . rand(100000, 999999);
            BankAccount::create([
                'user_id' => $data['user_id'],
                'account_number' => $accountNumber,
                'balance' => 0.00,
            ]);

            Log::info("Conta bancária criada para {$data['name']} (ID: {$data['user_id']}) - Número: $accountNumber");
        };

        // Configurar o consumo contínuo
        $rabbitMQ->consume($callback);
    }
}
```

#### 4. Service Provider para Automação

Crie `app/Providers/RabbitMQConsumerProvider.php`:

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

class RabbitMQConsumerProvider extends ServiceProvider
{
    public function boot()
    {
        // Iniciar o consumidor em um processo separado
        $this->startConsumer();
    }

    protected function startConsumer()
    {
        Log::info("Iniciando consumidor RabbitMQ automaticamente...");
        if (PHP_OS_FAMILY === 'Windows') {
            pclose(popen('start /B php artisan rabbitmq:consume-users', 'r'));
        } else {
            exec('php artisan rabbitmq:consume-users > /dev/null 2>&1 &');
        }
    }

    public function register()
    {
        //
    }
}
```

Registre o provider em `config/app.php`:

```php
'providers' => [
    // Outros providers...
    App\Providers\RabbitMQConsumerProvider::class,
],
```

---

## Fluxo de Funcionamento

### Produtor (Autenticação e Registros)

1. Uma requisição POST é feita para `/api/register` com os dados do usuário (`name`, `email`, `password`).
2. O `UserController` valida os dados, cria o usuário no banco de dados e publica uma mensagem JSON na fila `user_registration` via `RabbitMQService`.
3. A mensagem contém `user_id`, `name` e `email`.

### Consumidor Automatizado (Contas Bancárias)

1. Quando o Laravel é inicializado (ex.: `php artisan serve`), o `RabbitMQConsumerProvider` executa o comando `rabbitmq:consume-users` em segundo plano.
2. O comando inicia o `RabbitMQService`, que "escuta" a fila `user_registration` continuamente.
3. Ao receber uma mensagem, o callback decodifica o JSON, cria uma conta bancária no banco de dados e registra o evento nos logs.

---

## Testando o Sistema

### 1. Iniciar o Serviço de Contas Bancárias

No projeto de Contas Bancárias, inicie o Laravel:

```bash
php artisan serve
```

- O consumidor começará automaticamente a escutar a fila.

### 2. Cadastrar um Usuário

No projeto de Autenticação, envie uma requisição:

```bash
curl -X POST http://localhost:8000/api/register \
-H "Content-Type: application/json" \
-d '{"name": "João Silva", "email": "joao@example.com", "password": "12345678"}'
```

### 3. Verificar o Resultado

- No serviço de Autenticação: O usuário é cadastrado e a mensagem é enviada à fila.
- No serviço de Contas Bancárias: Verifique `storage/logs/laravel.log` para confirmar que a conta bancária foi criada automaticamente.

---

## Considerações

### Escalabilidade

- **Desenvolvimento**: O uso de `exec` ou `popen` funciona bem localmente, mas em produção, considere o Supervisor para gerenciar o consumidor:
  ```ini
  [program:rabbitmq-consumer]
  command=php /path/to/your/laravel-project/artisan rabbitmq:consume-users
  directory=/path/to/your/laravel-project
  autostart=true
  autorestart=true
  user=www-data
  redirect_stderr=true
  stdout_logfile=/path/to/your/laravel-project/storage/logs/rabbitmq-consumer.log
  ```

### Tratamento de Erros

- O `RabbitMQService` reconecta automaticamente em caso de falhas na conexão com o RabbitMQ.
- Logs são usados para monitoramento (`laravel.log`).

### Limitações

- Em ambientes Windows, o `popen` pode não ser tão estável quanto `exec` em Unix-like. Teste bem no seu ambiente.
- O consumidor roda em segundo plano e não bloqueia o servidor web, mas não há controle direto via interface (ex.: parar/reiniciar).

---

## Conclusão

Esta implementação oferece:

- **Envio**: Mensagens enviadas automaticamente ao RabbitMQ ao cadastrar usuários.
- **Consumo Automatizado**: Escuta contínua da fila, iniciada com o boot do Laravel, sem intervenção manual após o start.

Se precisar de ajustes, como adicionar mais filas ou melhorar a robustez, é só avisar!
