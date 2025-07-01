<?php

namespace Tests\Application\UseCases;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\InvalidParametersDataException;
use App\Application\UseCases\Wallet\CreateWalletCase;
use App\Domain\Interfaces\Repository\WalletRepositoryInterface;
use App\Infrastructure\DAO\WalletDAO;
use App\Infrastructure\Persistence\WalletRepository;
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

    public function setUp(): void
    {
        // Check if SQLite is available
        if (!in_array('sqlite', PDO::getAvailableDrivers())) {
            $this->markTestSkipped('SQLite PDO driver not available');
        }

        $loggerMock = $this->createMock(Logger::class);
        $loggerMock->method('info')->willReturn(true);

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

        $insertWallet = "INSERT INTO wallets (id, user_id, user_email, status) VALUES";
        $insertWallet .= "(1, 1, 'teste1@email.com', 1)";

        $queries = [
            $insertWallet
        ];

        foreach ($queries as $query) {
            $this->pdo->exec($query);
        }
        $this->walletRepository = new WalletRepository($loggerMock, $this->pdo);
        $this->walletRepositoryMock = $this->createMock(WalletRepositoryInterface::class);
        $this->walletDAO = new WalletDAO($loggerMock, $this->pdo);

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
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO);
        $this->walletRepositoryMock->expects($this->once())->method('save');
        $createWalletCase->execute($userId, $userEmail);
    }
    public function testShouldNotCreateWalletWithInvalidData()
    {
        $userEmail = 'teste@email.com';
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO);
        $this->walletRepositoryMock->expects($this->never())->method('save');
        $this->expectException(InvalidParametersDataException::class);
        $createWalletCase->execute(null, $userEmail);
    }

    public function testShouldNotCreateWalletWithInvalidEmail()
    {
        $userId = 2;
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO);
        $this->walletRepositoryMock->expects($this->never())->method('save');
        $this->expectException(InvalidParametersDataException::class);
        $createWalletCase->execute($userId, null);
    }

    public function testSouldNotCreateWalletWithAlreadyExistingEmail()
    {
        $userId = 1;
        $userEmail = 'teste1@email.com';
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO);
        $this->walletRepositoryMock->expects($this->never())->method('save');
        $createWalletCase->execute($userId, $userEmail);
    }
    public function testDatabaseIntegrationShouldCreateWallet()
    {
        $userId = 2;
        $userEmail = 'teste2@email.com';
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO);
        $this->walletRepositoryMock->expects($this->once())->method('save')->willReturn(true);
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

    public function testDatabaseNotIntegrationShouldCreateWallet()
    {
        $userId = 1;
        $userEmail = 'teste1@email.com';
        $createWalletCase = new CreateWalletCase($this->walletRepositoryMock, $this->walletDAO);
        $this->walletRepositoryMock->expects($this->once())->method('save')->willReturn(true);
        try {

            $createWalletCase->execute($userId, $userEmail);
        } catch (ApplicationException $e) {
            $sql = "SELECT * FROM wallets WHERE user_id = :userId AND user_email = :userEmail";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':userId', $userId, \PDO::PARAM_INT);
            $stmt->bindParam(':userEmail', $userEmail, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            $this->assertEmpty($result, 'Wallet should be created in the database');
        }


    }
}