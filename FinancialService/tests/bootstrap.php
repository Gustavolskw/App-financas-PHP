<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\MigratorConfiguration;

echo ('Bootstrap file loaded' . PHP_EOL);

call_user_func(function () {
    echo ('Setup database start ...' . PHP_EOL);

    $sqlite = file_get_contents(__DIR__ . '/../database/sqlite.db');

    $tempDB = tempnam(sys_get_temp_dir(), 'sqlite_');
    file_put_contents($tempDB, $sqlite);
    putenv("SQLITE_PATH=$tempDB");

    $pdo = new PDO("sqlite:$tempDB");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $arrVersions = [
        '20250611001025',
        '20250611001029',
        '20250611001030',
        '20250611001031',
        '20250611001032',
        '20250611001148',
        '20250611001149',
        '20250611001150',
        '20250611001247',
        '20250611001248',
        '20250611001249',
        '20250611001331',
        '20250611001332',
        '20250611001333',
        '20250611001334'
    ];

    echo ('Initial structure applied' . PHP_EOL);

    $connection = DriverManager::getConnection([
        'path'   => $tempDB,
        'driver' => 'pdo_sqlite',
    ]);

    $configArray = new ConfigurationArray([
        'migrations_namespace' => 'DoctrineMigrations',
        'migrations_directory' => __DIR__ . '/../database/migrations/',
        'table_storage' => [
            'table_name' => 'doctrineMigrationVersions',
        ],
    ]);

    $dependencyFactory = DependencyFactory::fromConnection(
        $configArray,
        new ExistingConnection($connection)
    );

    $metadataStorage = $dependencyFactory->getMetadataStorage();
    $metadataStorage->ensureInitialized();

    $executedMigrations = array_map(
        fn($migration) => $migration->getVersion(),
        $metadataStorage->getExecutedMigrations()->getItems()
    );

    $migrator = $dependencyFactory->getMigrator();

    echo 'Executando migrations específicas...' . PHP_EOL;

    foreach ($arrVersions as $version) {
        if (!in_array($version, $executedMigrations, true)) {
            echo "Migrando versão: $version" . PHP_EOL;
            $migrator->migrate($version, new MigratorConfiguration(false));
        } else {
            echo "Versão já aplicada: $version" . PHP_EOL;
        }
    }

    echo ('Setup database finish' . PHP_EOL);
});
