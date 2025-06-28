<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Migration;

echo ('Bootstrap file loaded' . PHP_EOL);

call_user_func(function () {
    echo ('Setup database start ...' . PHP_EOL);

    $sqlite = file_get_contents(__DIR__ . '/../database/sqlite.db');

    $tempDB = tempnam(sys_get_temp_dir(), 'sqlite_');
    file_put_contents($tempDB, $sqlite);
    putenv("SQLITE_PATH=$tempDB");

    $pdo = new PDO("sqlite:$tempDB");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insere versões de migrations pré-aplicadas
    $arrVersions = [
        '20231021014320',
        '20231026213933',
        '20231027000456',
        '20231027201744',
        '20231106192917',
        '20231107141555',
        '20231109135610',
        '20231117184350',
        '20231122131149',
        '20231124233739',
        '20231206195556',
        '20231207001839',
        '20240124205458',
        '20240126165421',
        '20240222111219',
        '20240226003427',
        '20240226003428',
        '20240226210007',
        '20240318171027',
        '20240411213456',
        '20240415165815',
        '20240415215953',
        '20240423143719',
        '20240429224649',
        '20240430161731',
        '20240430173516',
        '20240430203259',
        '20240502201828',
        '20240503222142',
        '20240504021334',
        '20240508175937',
        '20240509002632',
        '20240521214734',
        '20240522215237',
        '20240528195316',
        '20240604193255',
        '20240605202508',
        '20240606215317',
        '20240610205642',
        '20240612202252',
        '20240613150714',
        '20240613171548',
        '20240618163150',
        '20240624180000',
        '20240624182100',
        '20240625221509',
        '20240626144708',
        '20240702140745',
        '20240704131446',
        '20240711131554',
        '20240716123850',
        '20240725130346',
        '20240725192127',
        '20240726131840',
        '20240726175443',
        '20240729170840',
        '20240729202046',
        '20240730162637',
        '20240801175552',
        '20240801183626',
        '20240805203943',
        '20240807205420',
        '20240808205425',
        '20240808211951',
        '20240822122236',
        '20240822132617',
        '20240823125625',
        '20240823132957',
        '20240823190010',
        '20240903123618',
        '20240903132358',
        '20240905130007',
        '20240916140904',
        '20240924134715',
        '20240924202457',
        '20241002165709',
        '20241002165715',
        '20241002165717',
        '20241018131210',
        '20241018131211',
        '20241018131213',
        '20241021184335',
        '20241022133312',
        '20241023193323',
        '20241025130744',
        '20241025203223',
        '20241105122815',
        '20241105161828',
        '20241108140849',
        '20241108140912',
        '20241112120954',
        '20241112174823',
        '20241129133646',
        '20241205153056',
        '20241210163435',
        '20241211140908',
        '20241211145825',
        '20241217112151',
        '20241230194632',
        '20250102110656',
        '20250102115510',
        '20250102120134',
        '20250108123019',
        '20250109110706',
        '20250114132543',
        '20250114141709',
        '20250121113930',
        '20250210170750',
        '20250211134208',
        '20250218110343',
        '20250218124320',
        '20250304102620',
        '20250304110033',
        '20250311115203',
        '20250318173237',
    ];
    foreach ($arrVersions as $version) {
        $pdo->exec("INSERT INTO doctrineMigrationVersions (version) VALUES ('$version')");
    }

    echo ('Initial structure applied' . PHP_EOL);

    $db = DriverManager::getConnection([
        'path'   => $tempDB,
        'driver' => 'pdo_sqlite',
    ]);

    $config = new Configuration($db);
    $config->setMigrationsTableName('doctrineMigrationVersions');
    $config->setMigrationsNamespace('DoctrineMigrations');
    $config->setMigrationsDirectory(__DIR__ . '/../database/migrations/');
    $config->registerMigrationsFromDirectory($config->getMigrationsDirectory());

    echo 'Run migrations' . PHP_EOL;
    (new Migration($config))->migrate();

    $pdo = null;
    $db->close();

    echo ('Setup database finish' . PHP_EOL);
});
