<?php

namespace App\Controller;

use Swoole\Http\Response;

class HomeController {
    public function index(Response $response) {
        $response->header("Content-Type", "application/json");
        $response->end(json_encode(["message" => "Hello, Swoole!"]));
    }
}