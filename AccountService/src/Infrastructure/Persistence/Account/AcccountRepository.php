<?php

namespace App\Infrastructure\Persistence\Account;

use App\Domain\Account\Account;
use App\Infrastructure\Persistence\PersistenceRepository;
use DateTimeImmutable;
use PDO;
use PDOException;

class AcccountRepository extends PersistenceRepository
{
    public function findAll(): ?array
    {
        $sql = "SELECT * FROM accounts";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result){
            return array_map(function ($item) {
                return new Account(
                    $item['id'] ?? null,
                    $item['user_id'] ?? null,
                    $item['user_email'] ?? null,
                    $item['name'] ?? null,
                    $item['description'] ?? null,
                    $item['status'] ?? null,
                    new DateTimeImmutable($item['created_at']),
                    new DateTimeImmutable($item['updated_at']),
                );
            }, $result);
        }
        return null;
    }

    /**
     * @throws \Exception
     */
    public function findById($id):?Account
    {
        $sql = "SELECT * FROM accounts WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result){
            return new Account(
                $result['id'] ?? null,
                $result['user_id'] ?? null,
                $result['user_email'] ?? null,
                $result['name'] ?? null,
                $result['description'] ?? null,
                $result['status'] ?? null,
                new DateTimeImmutable($result['created_at']),
                new DateTimeImmutable($result['updated_at']),
            );
        }
        return null;

    }



    public function searchedQuery($sql, $args = []): ?array
    {
        $stmt = $this->getConnection()->prepare($sql);
        foreach ($args as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if($result){
            return array_map(function ($item) {
                return new Account(
                    $item['id'] ?? null,
                    $item['user_id'] ?? null,
                    $item['user_email'] ?? null,
                    $item['name'] ?? null,
                    $item['description'] ?? null,
                    $item['status'] ?? null,
                    new DateTimeImmutable($item['created_at']),
                    new DateTimeImmutable($item['updated_at']),
                );
            }, $result);
        }
        return null;
    }

    public function createAccount(Account $account) : ?Account
    {
        $stmt = $this->getConnection()->prepare('INSERT INTO accounts (user_id, user_email, name, description, status) VALUES (:user_id, :user_email, :name, :description, :status)');
        $stmt->bindValue(':user_id', $account->getUserId(), PDO::PARAM_INT);
        $stmt->bindValue(':user_email', $account->getUserEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':name', $account->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':description', $account->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $account->getStatus(), PDO::PARAM_STR);
        $insertResult = $stmt->execute();
        if($insertResult === false) {
            throw new PDOException('Failed to insert account: ' . implode(', ', $stmt->errorInfo()));
        }
        $accountId = $this->getConnection()->lastInsertId();

        $stmt = $this->getConnection()->prepare('SELECT * FROM accounts WHERE id = :id');
        $stmt->bindValue(':id', $accountId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result ) 
        {
            return new Account($$result['id'], $result['userId'],$result['userEmail'],  $result['name'], $result['description'], $result['status'], $result['created_at'], $result['updated_at']);
        }
        throw new PDOException('Failed to fetch account after insert: ' . implode(', ', $stmt->errorInfo()));
    }


}