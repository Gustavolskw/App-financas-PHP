Para ajustar o ponto de entrada `server.php` de modo a permitir a criação de múltiplas instâncias do serviço `auth-service` nas portas 9501, 9503 e 9504, o ideal é utilizar a variável de ambiente `PORT`, que você pode definir no `docker-compose.yml` para configurar dinamicamente a porta em que o servidor OpenSwoole irá escutar.

### Atualização no `server.php`

Você pode alterar o código para que ele use a variável de ambiente `PORT`, o que permitirá que o servidor escute na porta correta para cada instância.

Aqui está como você pode modificar o seu `server.php` para utilizar a variável de ambiente `PORT`:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use Auth\Router\Routes;
use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

// Carrega as variáveis de ambiente do arquivo .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Verifica se JWT_SECRET está definido
if (!isset($_ENV['JWT_SECRET']) || empty($_ENV['JWT_SECRET'])) {
    die('Erro: A variável de ambiente JWT_SECRET não está definida no .env');
}

// Obtém a porta do ambiente ou usa a porta 9501 como padrão
$port = $_ENV['PORT'] ?? 9501;

// Cria o servidor OpenSwoole
$server = new Server('0.0.0.0', $port);

// Configure o servidor para usar multi-threading e task workers
$server->set([
    'worker_num' => 4,  // Número de processos de trabalho (default é 1)
    'task_worker_num' => 2, // Número de workers de tarefa (para processamento assíncrono)
    'daemonize' => false,  // Definir como true para rodar como um daemon (em segundo plano)
    'max_request' => 10000,  // Número máximo de requisições por worker antes de reiniciar
]);

// Define o callback da tarefa - onde a lógica da tarefa em segundo plano é tratada
$server->on('task', function (Server $server, $task_id, $worker_id, $data) {
    // Aqui, você trata a tarefa
    echo "Task {$task_id} is being processed\n";

    // Fazer algo com os dados da tarefa
    // Por exemplo, vamos apenas retornar os dados da tarefa como resultado
    return "Task {$task_id} finished processing";
});

// Define o callback de conclusão - é chamado quando uma tarefa termina
$server->on('finish', function (Server $server, $task_id, $data) {
    // Aqui, você pode tratar o resultado da tarefa
    echo "Task {$task_id} finished with data: {$data}\n";
});

$server->on('start', function (Server $server) {
    echo "AuthService running on http://0.0.0.0:{$port}\n";
});

$server->on('request', function (Request $request, Response $response) {
    $routes = new Routes();
    $routes->handle($request, $response);
});

$server->start();
```

### O que foi modificado:

1. **Porta dinâmico**: A variável de ambiente `PORT` é agora utilizada para definir a porta em que o servidor OpenSwoole irá escutar. Se a variável `PORT` não estiver definida, o servidor usará a porta 9501 por padrão. 

2. **Configuração de instâncias**: Como a porta agora pode ser definida no arquivo `docker-compose.yml`, isso permite que você tenha 3 instâncias do `auth-service`, cada uma escutando em uma porta diferente.

### Atualização no `docker-compose.yml`

Agora, vamos garantir que cada instância do serviço `auth-service` tenha a variável de ambiente `PORT` configurada corretamente. Aqui está a atualização do `docker-compose.yml` para rodar 3 instâncias do `auth-service`, cada uma com uma porta diferente.

```yaml
auth-service:
  build:
    context: ./AuthService
    dockerfile: Dockerfile
  container_name: auth-service-1
  ports:
    - "9501:9501"
  environment:
    - PORT=9501  # Porta para a primeira instância
  volumes:
    - ./src:/app
  env_file:
    - ./AuthService/.env
  command: ["sh", "-c", "sleep 10 && php server.php"]
  depends_on:
    - rabbitmq
  networks:
    - fin-app

auth-service-2:
  build:
    context: ./AuthService
    dockerfile: Dockerfile
  container_name: auth-service-2
  ports:
    - "9503:9503"
  environment:
    - PORT=9503  # Porta para a segunda instância
  volumes:
    - ./src:/app
  env_file:
    - ./AuthService/.env
  command: ["sh", "-c", "sleep 10 && php server.php"]
  depends_on:
    - rabbitmq
  networks:
    - fin-app

auth-service-3:
  build:
    context: ./AuthService
    dockerfile: Dockerfile
  container_name: auth-service-3
  ports:
    - "9504:9504"
  environment:
    - PORT=9504  # Porta para a terceira instância
  volumes:
    - ./src:/app
  env_file:
    - ./AuthService/.env
  command: ["sh", "-c", "sleep 10 && php server.php"]
  depends_on:
    - rabbitmq
  networks:
    - fin-app
```

### Explicação das mudanças:

1. **Várias instâncias**: Definimos três instâncias do `auth-service`, cada uma com um nome de container único (`auth-service-1`, `auth-service-2`, `auth-service-3`) e portas diferentes (`9501`, `9503`, `9504`).

2. **Variáveis de ambiente**: A variável de ambiente `PORT` é definida para cada instância para garantir que cada uma escute em uma porta diferente.

3. **Exposição de portas**: As portas no host (`9501`, `9503`, `9504`) estão mapeadas para as portas internas de cada container, permitindo que o NGINX balanceie as requisições entre as instâncias.

### Estrutura de Diretórios

A estrutura de diretórios permanece a mesma, com o `docker-compose.yml` e o `server.php` ajustados para garantir que cada instância do serviço `auth-service` utilize a porta correta.

```
App-financas-OpenSwoole-PHP/
├── AuthService/
│   └── Dockerfile
├── AccountService/
│   └── Dockerfile
├── nginx/
│   ├── nginx.conf
│   └── default.conf
├── mysql/
├── rabbitmq/
├── redis/
├── mongodb/
├── docker-compose.yml
└── src/
```

### Como rodar o projeto

Agora, para rodar as 3 instâncias do `auth-service`, basta executar o seguinte comando:

```bash
docker-compose up --build
```

Isso iniciará as três instâncias do `auth-service` nas portas 9501, 9503 e 9504, e o NGINX será configurado para balancear as requisições entre elas.