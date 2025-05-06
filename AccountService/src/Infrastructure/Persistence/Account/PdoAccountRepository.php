<?php

namespace App\Infrastructure\Persistence\Account;

use App\Domain\Entity\Account;
use App\Domain\Interfaces\AccountRepository;
use App\Infrastructure\Persistence\PersistenceRepository;
use DateTimeImmutable;
use Exception;
use PDO;
use PDOException;

class PdoAccountRepository extends PersistenceRepository implements AccountRepository
{
    public function findAll(): ?array
    {
        $sql = 'SELECT * FROM accounts';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            return array_map(
                function ($item) {
                    return $this->buildAccount($item);
                },
                $result
            );
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public function findById($id):?Account
    {
        $sql = "SELECT * FROM accounts WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $this->buildAccount($result);
        }
        return null;
    }



    public function searchedQuery($sql, $args = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        foreach ($args as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            return array_map(
                function ($item) {
                      return $this->buildAccount($item);
                },
                $result
            );
        }
        return null;
    }

    /**
     * @throws Exception
     */
    public function save(Account $account) : ?Account
    {
        $sql = 'INSERT INTO accounts (userId, userEmail, name, description, status) VALUES (:userId, :userEmail, :name, :description, :status)';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':userId', $account->getUserId(), PDO::PARAM_INT);
        $stmt->bindValue(':userEmail', $account->getUserEmail(), PDO::PARAM_STR);
        $stmt->bindValue(':name', $account->getName(), PDO::PARAM_STR);
        $stmt->bindValue(':description', $account->getDescription(), PDO::PARAM_STR);
        $stmt->bindValue(':status', $account->getStatus(), PDO::PARAM_STR);
        $insertResult = $stmt->execute();
        if ($insertResult === false) {
            throw new PDOException('Failed to insert account: ' . implode(', ', $stmt->errorInfo()));
        }
        $accountId = $this->pdo->lastInsertId();

      

        $stmt = $this->pdo->prepare('SELECT * FROM accounts WHERE id = :id');
        $stmt->bindValue(':id', $accountId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return $this->buildAccount($result);
        }
        throw new PDOException('Failed to fetch account after insert: ' . implode(', ', $stmt->errorInfo()));
    }

    public function updateAccount(Account $account, int $id): ?Account
    {

        $sql = 'UPDATE accounts SET name = :name, description = :description WHERE id = :id';
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':name', $account->getName(), PDO::PARAM_STR);
            $stmt->bindValue(':description', $account->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $this->pdo->commit();

            return new Account(
                $id,
                $account->getUserId(),
                $account->getUserEmail(),
                $account->getName(),
                $account->getDescription(),
                $account->getStatus(),
                null,
                null
            );
        } catch (Exception $e) {
            throw new PDOException("Failed to update account: " . $e->getMessage());
        }
    }

    public function deleteAccount(int $id): bool
    {
        $sql = 'UPDATE accounts SET status = 0 WHERE id = :id';
        try {
            $this->pdo->beginTransaction();
            $smtp = $this->pdo->prepare($sql);
            $smtp->bindValue(':id', $id, PDO::PARAM_INT);
            $smtp->execute();

            return $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new PDOException("Failed to delete account ". $e->getMessage());
        }
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

    public function countUserAccounts(int $userId): mixed
    {
        $sql = "SELECT COUNT(*) as count FROM accounts WHERE userId = :userId";
        $smtp = $this->pdo->prepare($sql);
        $smtp->bindValue(':userId', $userId, PDO::PARAM_INT);
        $smtp->execute();
        return $smtp->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteAccountsByUserId(int $userId, string $userEmail): bool
    {
        $sql = 'UPDATE accounts SET status = false WHERE userId = :userId AND userEmail = :userEmail';
        try{
            $this->pdo->beginTransaction();
            $smtp = $this->pdo->prepare($sql);
            $smtp->bindValue(':userId', $userId, PDO::PARAM_INT);
            $smtp->bindValue(':userEmail', $userEmail);
            $smtp->execute();
            return $this->pdo->commit();


        }catch (Exception $e){
            $this->pdo->rollBack();
            throw new PDOException("Failed to delete accounts by user id: " . $e->getMessage());
        }
    }

    public function veirifAccount(int $id): bool
    {
        $sql = 'SELECT EXISTS (SELECT 1 FROM ACCOUNT_SERVICE.accounts WHERE id = :id) as existe;';
        try{
            $smtp = $this->pdo->prepare($sql);
            $smtp->bindValue(':id', $id, PDO::PARAM_INT);
            $smtp->execute();
            $result = $smtp->fetch(PDO::FETCH_ASSOC);
            return $result['existe'];
        }catch (Exception $e){
            throw new PDOException("Failed to delete accounts by id: " . $e->getMessage());
        }
    }

    public function updateUserAccountsStatus(int $userId, bool $status):void
    {
        $sql = "UPDATE accounts SET status = :status WHERE userId = :userId";
        try{
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $status, PDO::PARAM_BOOL);
            $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $this->pdo->commit();
        }catch (Exception $e){
            $this->pdo->rollBack();
            throw new PDOException("Failed to update user accounts status: " . $e->getMessage());
        }
    }
}
