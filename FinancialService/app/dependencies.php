<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use App\Domain\Interfaces\DAO\WalletDAOInterface;
use App\Domain\Interfaces\Repository\PersistenceErrorLogRepositoryInterface;
use App\Domain\Interfaces\Repository\WalletRepositoryInterface;
use App\Infrastructure\DAO\WalletDAO;
use App\Infrastructure\Persistence\Wallet\PdoWalletRepository;
use App\Infrastructure\Persistence\WalletRepository;
use DI\ContainerBuilder;
use MongoDB\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true,
                'logError' => true,
                'logErrorDetails' => true,
                'logger' => [
                    'name' => 'app',
                    'path' => __DIR__ . '/../logs/app.log',
                    'level' => \Monolog\Logger::DEBUG,
                ],
            ]);
        },
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $loggerSettings = $settings->get('logger');

            $logger = new Logger($loggerSettings['name']);
            $logger->pushProcessor(new UidProcessor());
            $logger->pushHandler(new StreamHandler($loggerSettings['path'], $loggerSettings['level']));

            return $logger;
        },
        PDO::class => function (ContainerInterface $c) {
            $dbConfig = $c->get('db');
            $dsn = "{$dbConfig['driver']}:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
            return new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['flag']);
        },
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

        'mongo' => [
            'uri' => getenv('MONGO_URI') ?: 'mongodb://root:password@mongodb-fin:27017',
            'database' => getenv('MONGO_DB') ?: 'financial-app',
        ],


        Client::class => function (ContainerInterface $c) {
            $mongoConfig = $c->get('mongo');
            return new Client($mongoConfig['uri']);
        },

        MongoDB\Database::class => function (ContainerInterface $c) {
            $mongoConfig = $c->get('mongo');
            $client = $c->get(Client::class);
            return $client->selectDatabase($mongoConfig['database']);
        },

        WalletRepositoryInterface::class => DI\autowire(WalletRepository::class),
        WalletDAOInterface::class => DI\autowire(WalletDAO::class),
        PersistenceErrorLogRepositoryInterface::class => DI\autowire(PersistenceErrorLogRepositoryInterface::class),
    ]);
};
