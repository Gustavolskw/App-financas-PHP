<?php

namespace App\Infrastructure\Persistence;

use App\Infrastructure\Database\Database;
use PDO;
use Psr\Log\LoggerInterface;

abstract class PersistenceRepository extends Database
{
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    protected function getConnection(): PDO
    {
        return $this->getPDO();
    }
}