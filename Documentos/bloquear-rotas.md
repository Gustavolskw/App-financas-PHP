Para proteger múltiplas rotas específicas no **Serviço de Caixas**, podemos ajustar o `Routes/api.php` para verificar uma lista de rotas protegidas, em vez de apenas uma. Isso mantém a flexibilidade para definir exatamente quais rotas exigem validação do token JWT, enquanto as demais permanecem livres.

Aqui está o `Routes/api.php` atualizado para proteger múltiplas rotas específicas:

---

### Routes/api.php (Bloqueio de Múltiplas Rotas Específicas)

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

    // Normaliza a URI e o método HTTP
    $uri = rtrim($request->server['request_uri'] ?? '/', '/');
    $method = strtoupper($request->server['request_method'] ?? 'GET');

    // Define as rotas protegidas (método e URI)
    $protectedRoutes = [
        ['method' => 'POST', 'uri' => '/caixas'],
        ['method' => 'PUT', 'uri' => '/caixas/{id}'],
        ['method' => 'DELETE', 'uri' => '/caixas/{id}'],
    ];

    // Aplica validação de token apenas para rotas protegidas
    $requiresAuth = false;
    foreach ($protectedRoutes as $protected) {
        $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $protected['uri']);
        if ($method === $protected['method'] && preg_match("#^$routePattern$#", $uri)) {
            $requiresAuth = true;
            break;
        }
    }

    if ($requiresAuth) {
        $token = str_replace('Bearer ', '', $request->header['authorization'] ?? '');
        try {
            $decoded = JWT::decode($token, new Key($_ENV['JWT_SECRET'], 'HS256'));
            $request->user_id = $decoded->id; // Adiciona o ID do usuário ao request
        } catch (\Exception $e) {
            $response->status(401);
            $response->header('Content-Type', 'application/json');
            $response->end(json_encode(['error' => 'Token inválido ou expirado']));
            return;
        }
    }

    // Rotas do serviço de caixas
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

### Explicação

1. **Lista de Rotas Protegidas**:

   - O array `$protectedRoutes` contém as rotas que exigem autenticação, especificadas por método (`method`) e URI (`uri`). Neste exemplo, protegi `POST /caixas`, `PUT /caixas/{id}` e `DELETE /caixas/{id}`.

2. **Validação Dinâmica**:

   - O código percorre `$protectedRoutes` e usa `preg_match` para verificar se a URI atual corresponde a uma rota protegida, considerando os parâmetros dinâmicos (ex.: `{id}`). Se houver correspondência e o método for o mesmo, `$requiresAuth` é definido como `true`.

3. **Aplicação do Token**:

   - A validação do token só ocorre se `$requiresAuth` for `true`, ou seja, se a combinação de método e URI estiver em `$protectedRoutes`.

4. **Flexibilidade**:
   - Para adicionar mais rotas protegidas, basta incluir novas entradas no array `$protectedRoutes`. Por exemplo, para proteger `GET /caixas`, adicione `['method' => 'GET', 'uri' => '/caixas']`.

---

### Testando

- **POST /caixas (sem token, bloqueado)**:

  ```bash
  curl -X POST http://127.0.0.1:9502/caixas \
  -H "Content-Type: application/json" \
  -d '{"name": "Caixa Corrente"}'
  ```

  Resposta: `{"error": "Token inválido ou expirado"}`

- **POST /caixas (com token, permitido)**:

  ```bash
  curl -X POST http://127.0.0.1:9502/caixas \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {token}" \
  -d '{"name": "Caixa Corrente"}'
  ```

  Resposta: Criação do caixa.

- **PUT /caixas/1 (sem token, bloqueado)**:

  ```bash
  curl -X PUT http://127.0.0.1:9502/caixas/1 \
  -H "Content-Type: application/json" \
  -d '{"name": "Caixa Atualizado"}'
  ```

  Resposta: `{"error": "Token inválido ou expirado"}`

- **GET /caixas (sem token, permitido)**:

  ```bash
  curl -X GET http://127.0.0.1:9502/caixas
  ```

  Resposta: Lista de caixas.

- **GET /caixas/1 (sem token, permitido)**:
  ```bash
  curl -X GET http://127.0.0.1:9502/caixas/1
  ```
  Resposta: Detalhes do caixa.

---

### Observações

- **Parâmetros Dinâmicos**: O uso de `preg_match` permite que rotas com parâmetros (ex.: `/caixas/{id}`) sejam corretamente identificadas como protegidas.
- **Manutenção**: Adicionar ou remover rotas protegidas é simples, apenas editando `$protectedRoutes`.

Se precisar de mais exemplos ou ajustes, é só avisar!
