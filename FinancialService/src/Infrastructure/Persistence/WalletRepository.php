<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Entity\Wallet;
use App\Domain\Exception\PersistenceException;
use App\Domain\Interfaces\Repository\WalletRepositoryInterface;

class WalletRepository extends PersistenceRepository implements WalletRepositoryInterface
{

    public function save(Wallet $wallet): bool
    {
        try{
            $this->pdo->beginTransaction();
            $sql = "INSERT INTO wallets (user_id, user_email, name, description, status) VALUES (:user_id, :user_email, :name, :description, :status)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':user_id', $wallet->getUserId());
            $stmt->bindValue(':user_email', $wallet->getUserEmail());
            $stmt->bindValue(':name', $wallet->getName());
            $stmt->bindValue(':description', $wallet->getDescription());
            $stmt->bindValue(':status', $wallet->getStatus());
            $stmt->execute();
            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new PersistenceException("Failed to start transaction: " . $e->getMessage(), null);
        }

    }
}