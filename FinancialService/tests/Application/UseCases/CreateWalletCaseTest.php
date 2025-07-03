<?php

namespace Tests\Application\UseCases;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\InvalidParametersDataException;
use App\Application\UseCases\Wallet\CreateWalletCase;
use App\Domain\Interfaces\Repository\WalletRepositoryInterface;
use App\Infrastructure\DAO\WalletDAO;
use App\Infrastructure\Persistence\PersistenceErrorLogRepository;
use App\Infrastructure\Persistence\WalletRepository;
use MongoDB\Database;
use Monolog\Logger;
use PDO;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Tests\Util\DatabaseUtils;

class CreateWalletCaseTest extends TestCase
{
    use DatabaseUtils;

    private PDO $pdo;
    private CreateWalletCase $createWalletCase;
    private WalletRepository $walletRepository;
    private MockObject $walletRepositoryMock;
    private WalletDAO $walletDAO;
    private Logger $logger;
    private PersistenceErrorLogRepository $errorLogRepository;
    private Database $mongoDB;

    public function setUp(): void
    {
        // Check if SQLite is available
        if (!in_array('sqlite', PDO::getAvailableDrivers())) {
            $this->markTestSkipped('SQLite PDO driver not available');
        }

        $this->logger = $this->createMock(Logger::class);
        $this->logger->method('critical');

        $sqlitePath = getenv('SQLITE_PATH');
        if (!$sqlitePath) {
            $this->markTestSkipped('SQLITE_PATH environment variable not set');
        }

        $this->pdo = new PDO("sqlite:$sqlitePath");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $tables = [
            'wallets',
        ];
        $this->removeRelationshipIndex($tables, $this->pdo);
        $this->deleteAllRecords($tables, $this->pdo);

        $insertWallet = "INSERT INTO wallets (id, user_id, user_email, name, description, status) VALUES";
        $insertWallet .= "(1, 1, 'teste1@email.com','teste', 'teste-descricao', 1)";

        $queries = [
            $insertWallet
        ];

        foreach ($queries as $query) {
            $this->pdo->exec($query);
        }
        $this->walletRepository = new WalletRepository($this->logger, $this->pdo);
        $this->walletRepositoryMock = $this->createMock(WalletRepositoryInterface::class);
        $this->mongoDB = $this->createMock(Database::class);
        $this->walletDAO = new WalletDAO($this->logger, $this->pdo);
        $this->errorLogRepository = new PersistenceErrorLogRepository($this->mongoDB);


    }

    public function tearDown(): void
    {
        if (isset($this->pdo)) {
            $tables = [
                'wallets',
            ];
            $this->deleteAllRecords($tables, $this->pdo);
            $this->pdo->exec("DELETE FROM sqlite_sequence");
        }
    }


    public function testShouldCreateWallet()
    {
        $userId = 2;
        $userEmail = 'teste@email.com';
        $this->walletRepositoryMock->method('save')->willReturn(true);
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO, $this->logger, $this->errorLogRepository);
        $this->walletRepositoryMock->expects($this->once())->method('save');
        $createWalletCase->execute($userId, $userEmail);
    }
    public function testShouldNotCreateWalletWithInvalidData()
    {
        $userEmail = 'teste@email.com';
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO, $this->logger, $this->errorLogRepository);
        $this->walletRepositoryMock->expects($this->never())->method('save');
        $this->expectException(InvalidParametersDataException::class);
        $createWalletCase->execute(0, $userEmail);
    }

    public function testShouldNotCreateWalletWithInvalidEmail()
    {
        $userId = 2;
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO, $this->logger, $this->errorLogRepository);
        $this->walletRepositoryMock->expects($this->never())->method('save');
        $this->expectException(InvalidParametersDataException::class);
        $createWalletCase->execute($userId, '');
    }

    public function testShouldNotCreateWalletWithAlreadyExistingEmail()
    {
        $userId = 1;
        $userEmail = 'teste1@email.com';
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO, $this->logger, $this->errorLogRepository);
        $this->walletRepositoryMock->expects($this->never())->method('save');
        $this->expectException(InvalidParametersDataException::class);
        $createWalletCase->execute($userId, $userEmail);
    }
    public function testDatabaseIntegrationShouldCreateWallet()
    {
        $userId = 3;
        $userEmail = 'teste3@email.com';
        $createWalletCase = new CreateWalletCase($this->walletRepository, $this->walletDAO, $this->logger, $this->errorLogRepository);
        $createWalletCase->execute($userId, $userEmail);
        $sql = "SELECT * FROM wallets WHERE user_id = :userId AND user_email = :userEmail";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
        $stmt->bindParam(':userEmail', $userEmail, \PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        $this->assertNotEmpty($result, 'Wallet should be created in the database');
        $this->assertEquals($userId, $result['user_id'], 'User ID should match');
        $this->assertEquals($userEmail, $result['user_email'], 'User email should match');
        $this->assertEquals(1, $result['status'], 'Wallet status should be active');
    }
}