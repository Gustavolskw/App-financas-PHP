<?php

use App\Controller\HomeController;
use Swoole\Http\Request;
use Swoole\Http\Response;

return function (Request $request, Response $response) {
    $uri = isset($request->server['request_uri']) ? $request->server['request_uri'] : '/';

    if ($uri === '/') {
        (new HomeController())->index($response);
        return;
    }

    $response->status(404);
    $response->header("Content-Type", "application/json");
    $response->end(json_encode(["error" => "Rota não encontrada"]));
};