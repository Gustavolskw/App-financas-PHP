<?php

namespace App\Infrastructure\Persistence\Account;

use App\Domain\Account\Account;
use App\Infrastructure\Persistence\PersistenceRepository;
use DateTimeImmutable;
use Exception;
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
                return $this->buildAccount($item);
            }, $result);
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public function findById($id):?Account
    {
        $sql = "SELECT * FROM accounts WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if($result){
            return $this->buildAccount($result);
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
            return array_map(/**
             * @throws \Exception
             */ function ($item) {
                return $this->buildAccount($item);
            }, $result);
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public function createAccount(Account $account) : ?Account
    {
        $stmt = $this->getConnection()->prepare('INSERT INTO accounts (userId, userEmail, name, description, status) VALUES (:userId, :userEmail, :name, :description, :status)');
        $stmt->bindValue(':userId', $account->getUserId(), PDO::PARAM_INT);
        $stmt->bindValue(':userEmail', $account->getUserEmail(), PDO::PARAM_STR);
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
            return $this->buildAccount($result);
        }
        throw new PDOException('Failed to fetch account after insert: ' . implode(', ', $stmt->errorInfo()));
    }

    /**
     * @param mixed $result
     * @return Account
     * @throws Exception
     */
    public function buildAccount(mixed $result): Account
    {
        return new Account(
            $result['id'] ?? null,
            $result['userId'] ?? null,
            $result['userEmail'] ?? null,
            $result['name'] ?? null,
            $result['description'] ?? null,
            $result['status'] ?? null,
            $result['created_at'] ? new DateTimeImmutable($result['created_at']) : null,
            $result['updated_at'] ? new DateTimeImmutable($result['updated_at']) : null,
        );
    }


}