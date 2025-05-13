<?php

namespace App\Infrastructure\Persistence\Transaction;

use App\Domain\Entity\Account;
use App\Domain\Entity\Category;
use App\Domain\Entity\Transaction;
use App\Domain\Interfaces\TransactionRepository;
use App\Infrastructure\Persistence\PersistenceRepository;
use Exception;

class PdoTransactionRepository extends PersistenceRepository implements TransactionRepository
{

    public function findAll(): array
    {
        $sql = "
        SELECT 
            t.*, 
            a.id AS account_id, a.user_id, a.user_email, a.name AS account_name, 
            a.description AS account_description, a.status AS account_status, 
            a.created_at AS account_created_at, a.updated_at AS account_updated_at,
            c.id AS category_id, c.name AS category_name
        FROM transactions t
        INNER JOIN accounts a ON t.account_id = a.id
        INNER JOIN categories c ON t.category_id = c.id
    ";

        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return array_map([$this, 'buildTransactionWithJoinedData'], $result);
    }

    public function findById(int $id): ?Transaction
    {
        // TODO: Implement findById() method.
    }

    public function findByDateRange(\DateTime $startDate, \DateTime $endDate): array
    {
        // TODO: Implement findByDateRange() method.
    }

    public function findByParams(array $params): array
    {
        // TODO: Implement findByParams() method.
    }

    public function create(Transaction $transaction): bool
    {
        // TODO: Implement create() method.
    }

    public function update(Transaction $transactionUpdate, int $id): bool
    {
        // TODO: Implement update() method.
    }

    /**
     * @throws Exception
     */
    private function buildTransactionWithJoinedData(array $data): Transaction
    {
        $account = new Account(
            (int)$data['account_id'],
            (int)$data['user_id'],
            $data['user_email'],
            $data['account_name'],
            $data['account_description'],
            (bool)$data['account_status'],
            new \DateTimeImmutable($data['account_created_at']),
            new \DateTimeImmutable($data['account_updated_at'])
        );
        $category = new Category(
            (int)$data['category_id'],
            $data['category_name']
        );
        return new Transaction(
            (int)$data['id'],
            $account,
            (bool)$data['type'],
            $category,
            new \Money\Money($data['amount'], new \Money\Currency('BRL')), // Adjust currency as needed
            new \DateTime($data['date'])
        );
    }

}