<?php
namespace Acc\Router;

use Acc\Controllers\AccountController;
use Acc\DTO\HttpResponse;
use Acc\JWT\UtilJwt;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

class Routes
{

    private $utilJwt;
    private $httpResponse;

    public function __construct()
    {
        $this->utilJwt = new UtilJwt();
        $this->httpResponse = new HttpResponse();
    }
    private function validateToken(Request $request): bool
    {
        $authHeader = $request->header['authorization'] ?? '';


        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return false;
        }

        $token = $matches[1];
        try {
            $decoded = $this->utilJwt->decodeJwt($token);
            $request->user_id = $decoded->sub;
            return true;
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    private function validateAcessToRoute(Request $request, int $requiredRole): bool
    {
        $authHeader = $request->header['authorization'] ?? '';
        if ($authHeader == false || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return false;
        }
        $token = $matches[1];
        try {
            $decoded = $this->utilJwt->decodeJwt($token);
            $role = $decoded->role ?? null;
            return $role >= $requiredRole;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function handle(Request $request, Response $response): void
    {
        $uri = rtrim($request->server['request_uri'] ?? '/', '/');
        $method = strtoupper($request->server['request_method'] ?? 'GET');

        $routes = [
            'GET' => [
                '/accounts' => [AccountController::class, 'getAllAccounts'],
                '/account/{id}' => [AccountController::class, 'getAccount'],
                '/user/{id}/accounts' => [AccountController::class, 'getUserAccounts'],
            ],
            'POST' => [
                '/account' => [AccountController::class, 'createAccount'],
            ],
            'PUT' => [
                '/account/{id}' => [AccountController::class, 'updateAccount'],
                '/account/reactivate/{id}' => [AccountController::class, 'reactivateAccount'],
            ],
            'DELETE' => [
                '/account/{id}' => [AccountController::class, 'removeAccount'],
            ],
        ];
        $protectedRoutes = [
            ['method' => 'POST', 'uri' => '/account', 'role' => 1],
            ['method' => 'GET', 'uri' => '/account/{id}', 'role' => 1],
            ['method' => 'GET', 'uri' => '/user/{id}/accounts', 'role' => 1],
            ['method' => 'GET', 'uri' => '/accounts', 'role' => 2],
            ['method' => 'PUT', 'uri' => '/account/{id}', 'role' => 1],
            ['method' => 'PUT', 'uri' => '/account/reactivate/{id}', 'role' => 1],
            ['method' => 'DELETE', 'uri' => '/account/{id}', 'role' => 2],
        ];

        $requiresAuth = false;
        $requiredRole = null;
        foreach ($protectedRoutes as $protected) {
            $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $protected['uri']);
            if ($method === $protected['method'] && preg_match("#^$routePattern$#", $uri)) {
                $requiresAuth = true;
                $requiredRole = $protected['role'];
                break;
            }
        }

        if ($requiresAuth) {
            if (!$this->validateToken($request)) {
                $this->httpResponse->response(['error' => 'Token inválido ou expirado'], 401, $response);
                return;
            }
            if (!$this->validateAcessToRoute($request, $requiredRole)) {
                $this->httpResponse->response(['error' => 'Acesso negado: cargo insuficiente'], 403, $response);
                return;
            }
        }

        $params = [];
        foreach ($routes[$method] ?? [] as $route => $handler) {
            $routePattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
            if (preg_match("#^$routePattern$#", $uri, $matches)) {
                if (count($matches) > 1) {
                    array_shift($matches); // Remove o match completo, mantém apenas os grupos
                    $params = $matches;    // $params[0] será o ID
                }
                [$controllerClass, $methodName] = $handler;
                $controller = new $controllerClass();
                $controller->$methodName($request, $response, ...$params);
                return;
            }
        }


        $this->httpResponse->response(['error' => 'Rota não encontrada'], 404, $response);
    }
}