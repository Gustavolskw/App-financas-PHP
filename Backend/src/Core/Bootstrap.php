<?php

namespace App\Core;

use Swoole\Http\Server;

class Bootstrap {
    public static function run() {
        // Criando o servidor Swoole e configurando para usar múltiplos processos (workers)
        $server = new Server("0.0.0.0", 9501);

        $server->set([
            'worker_num' => 4,  // Número de workers
            'task_worker_num' => 2,  // Remova ou comente se não for usar tasks
        ]);
        $server->on("task", function ($server, $task_id, $src_worker_id, $data) {
            echo "Task $task_id: " . json_encode($data) . "\n";
            // Aqui você pode realizar tarefas assíncronas como enviar e-mails, processar imagens, etc.
            $server->finish("$task_id done");
        });

        $server->on("finish", function ($server, $task_id, $data) {
            echo "Task $task_id finished: $data\n";
        });
        // Evento de start do servidor
        $server->on("start", function (Server $server) {
            echo "Swoole HTTP Server started at http://127.0.0.1:9501\n";
        });

        // Evento de requisição
        $server->on("request", function ($request, $response) {
            // Carregar as rotas
            $routes = require __DIR__ . '/../Routes/api.php';
            $routes($request, $response);
        });

        // Iniciar o servidor
        $server->start();
    }
}