<?php

namespace App\Domain\Interfaces\Repository;

interface PersistenceErrorLogRepositoryInterface
{
    public function save(array $log): void;
    public function findAll(): array;
}