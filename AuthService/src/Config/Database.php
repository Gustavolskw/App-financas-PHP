<?php
namespace Auth\Config;

use Dotenv\Dotenv;
use PDO;
use PDOException;

class Database
{
    private static $pdo = null;

    public static function bootPDO(): void
    {
        // Carregar as variáveis de ambiente
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        try {
            // Criação da conexão PDO
            $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8";
            self::$pdo = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);

            // Definir o modo de erro para exceções
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            echo "Conexão com o banco de dados estabelecida com sucesso!";
        } catch (PDOException $e) {
            echo "Erro de conexão: " . $e->getMessage();
            exit;
        }
    }

    // Método para obter a instância do PDO
    public static function getPDO(): ?PDO
    {
        return self::$pdo;
    }
}
