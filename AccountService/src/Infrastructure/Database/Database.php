<?php

namespace App\Infrastructure\Database;

use PDO;
use PDOException;
use Dotenv\Dotenv;
use Psr\Log\LoggerInterface;

abstract class Database
{
    protected ?PDO $pdo = null;
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
            $dotenv->safeLoad();

            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_NAME'] ?? 'test';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASS'] ?? '';

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            $this->logger->error("Erro ao conectar ao banco de dados: " . $e->getMessage());
            exit;
        }
    }

    public function getPDO(): PDO
    {
        return $this->pdo;
    }
}

