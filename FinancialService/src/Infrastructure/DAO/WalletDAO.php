<?php

namespace App\Infrastructure\DAO;

use App\Domain\Entity\Wallet;
use App\Domain\Exception\PersistenceException;
use App\Domain\Interfaces\DAO\WalletDAOInterface;
use App\Domain\Utils\DomainObjectMapper;
use App\Infrastructure\Persistence\PersistenceRepository;

class WalletDAO extends PersistenceRepository implements WalletDAOInterface
{
    use DomainObjectMapper;

    public function getWalletByUserEmailAndUserId(string $email, int $userId): ?Wallet
    {
        $sql = "SELECT * FROM wallets WHERE user_email = :email AND user_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(!empty($result)){
            return $this->buildWallet($result);
        }
        return null;
    }

    public function getWalletByUserId(int $userId): ?Wallet
    {
        $sql = "SELECT * FROM wallets WHERE user_id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':userId', $userId);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(!empty($result)){
            return $this->buildWallet($result);
        }
        return null;
    }

    public function getWalletByUserEmail(string $userEmail): ?Wallet
    {
        $sql = "SELECT * FROM wallets WHERE user_email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $userEmail);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if(!empty($result)){
            return $this->buildWallet($result);
        }
        return null;
    }
}