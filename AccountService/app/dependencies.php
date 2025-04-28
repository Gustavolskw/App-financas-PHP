<?php

declare(strict_types=1);

use App\Application\Handlers\AccountHandler;
use App\Application\Handlers\AccountHandlerInterface;
use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Persistence\Account\AccountRepositoryHandler;
use App\Infrastructure\Persistence\Account\AccountRepositoryInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);

            $loggerSettings = $settings->get('logger');
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        'amqp' => [
            'host' => getenv('RABBITMQ_HOST'),
            'port' => getenv('RABBITMQ_PORT'),
            'user' => getenv('RABBITMQ_USER'),
            'password' => getenv('RABBITMQ_PASS'),
            'vhost' => getenv('RABBITMQ_VHOST'),
        ],

        AMQPStreamConnection::class => function (ContainerInterface $c) {
            // Retrieve RabbitMQ connection settings
            $config = $c->get('amqp');

            // Create and return AMQPStreamConnection with the configuration
            return new AMQPStreamConnection(
                $config['host'],
                $config['port'],
                $config['user'],
                $config['password'],
                $config['vhost']
            );
        },

        AMQPChannel::class => function (ContainerInterface $c) {
            // Get the AMQPStreamConnection instance and create a channel
            return $c->get(AMQPStreamConnection::class)->channel();
        },
        // Add PDO configuration
        PDO::class => function (ContainerInterface $c) {
            $dbConfig = $c->get('db');
            $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            return new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['flag']);
        },

        // Add Database Settings
        'db' => [
            'driver' => getenv('DB_DRIVER'),
            'host' => getenv('DB_HOST'),
            'port' => getenv('DB_PORT'),
            'dbname' => getenv('DB_NAME'),
            'username' => getenv('DB_USER'),
            'password' => getenv('DB_PASS'),
            'charset' => 'utf8mb4',
            'flag' => [
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_EMULATE_PREPARES => true,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ],
        ],


    ]);
    $containerBuilder->addDefinitions([
        AccountRepositoryInterface::class => DI\autowire(AccountRepositoryHandler::class),
        AccountHandlerInterface::class => DI\autowire(AccountHandler::class),

    ]);
};
