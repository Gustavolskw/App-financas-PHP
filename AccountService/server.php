<?php
require_once __DIR__ . '/vendor/autoload.php';
use Acc\Message\CaixaUserCreationQueueConsumer;
use Acc\Message\CaixaUserInactivationExchangeConsumer;
use Acc\Message\CaixaUserReactivationExchangeConsumer;
use Dotenv\Dotenv;
use Acc\Router\Routes;
use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;


$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();


if (!isset($_ENV['JWT_SECRET']) || empty($_ENV['JWT_SECRET'])) {
    die('Erro: A variável de ambiente JWT_SECRET não está definida no .env');
}

$server = new Server('0.0.0.0', 9502);


$server->set([
    'worker_num' => 8,
    'task_worker_num' => 16,
    'daemonize' => false,
    'max_request' => 10000,
]);

$server->on('task', function (Server $server, $task_id, $worker_id, $data) {


    if ($data['type'] === 'inactivation') {
        $consumer = new CaixaUserInactivationExchangeConsumer();
        $consumer->consumeFromExchange();
    }

    if ($data['type'] === 'reactivation') {
        $consumer = new CaixaUserReactivationExchangeConsumer();
        $consumer->consumeFromExchange();
    }

    if ($data['type'] === 'creation') {
        $consumer = new CaixaUserCreationQueueConsumer();
        $consumer->consumeFromQueue($data['queue']);
    }

    // Return task result
    return "Task {$task_id} finished";
});


$server->on('finish', function (Server $server, $task_id, $data) {

    echo "Task {$task_id} finished with data: {$data}\n";
});

$server->on('start', function (Server $server) {
    echo "AccountService running on http://0.0.0.0:9502\n";


    $server->task(['type' => 'inactivation']);
    $server->task(['type' => 'reactivation']);
    $server->task(['type' => 'creation', 'queue' => $_ENV['RABBITMQ_CONSUME_QUEUE']]);

});

$server->on('request', function (Request $request, Response $response) {
    $routes = new Routes();
    $routes->handle($request, $response);
});

$server->start();