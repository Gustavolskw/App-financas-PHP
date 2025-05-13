<?php

namespace App\Domain\Interfaces;

use App\Domain\Entity\Transaction;

interface TransactionRepository
{
 public function findAll(): array;
    public function findById(int $id): ?Transaction;
    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array;
    public function findByParams(array $params): array;
    public function create(Transaction $transaction): bool;
    public function update(Transaction $transactionUpdate, int $id): bool;
}