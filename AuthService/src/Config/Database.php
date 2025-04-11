<?php

namespace Auth\Config;

use PDO;
use PDOException;
use Dotenv\Dotenv;

class Database
{
    private static ?PDO $pdo = null;

    public static function getPDO(): ?PDO
    {
        if (self::$pdo === null) {
            try {
                // Carrega variáveis de ambiente (.env)
                $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
                $dotenv->safeLoad();

                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $dbname = $_ENV['DB_NAME'] ?? 'test';
                $username = $_ENV['DB_USER'] ?? 'root';
                $password = $_ENV['DB_PASS'] ?? '';

                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

                self::$pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
                exit; // evita continuar com conexão nula
            }
        }

        return self::$pdo;
    }
}
