<?php

declare(strict_types=1);

use App\Application\Actions\Account\ViewAccountAction;
use App\Application\Actions\Account\CreateAccountAction;
use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use App\Application\Controller\Account\AccountController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });


    $app->group("/accounts", function (RouteCollectorProxy $group) {
        $group->get("", AccountController::class . ":getAllAccounts");
        //$group->get("/{id}", ViewAccountAction::class);
        $group->post("", AccountController::class . ":createAccount");
        $group->patch("/{id}", AccountController::class . ":updateAccount");
    });
};
