<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Interfaces\Repository\PersistenceErrorLogRepositoryInterface;
use MongoDB\Collection;
use MongoDB\Database;

class PersistenceErrorLogRepository implements PersistenceErrorLogRepositoryInterface
{
    private Collection $collection;

    public function __construct(Database $mongoDb)
    {
        $this->collection = $mongoDb->selectCollection('error_logs');
    }

    public function save(array $log): void
    {
        $this->collection->insertOne($log);
    }

    public function findAll(): array
    {
        return $this->collection->find()->toArray();
    }
}