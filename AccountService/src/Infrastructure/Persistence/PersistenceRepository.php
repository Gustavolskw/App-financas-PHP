<?php

namespace App\Infrastructure\Persistence;

use PDO;
use Psr\Log\LoggerInterface;

abstract class PersistenceRepository
{
    protected PDO $pdo;
    protected LoggerInterface $logger;
    public function __construct(LoggerInterface $logger, PDO $pdo)
    {
        $this->logger = $logger;
        $this->pdo = $pdo;
    }
}
