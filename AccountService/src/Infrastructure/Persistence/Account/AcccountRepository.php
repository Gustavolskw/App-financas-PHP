<?php

namespace App\Infrastructure\Persistence\Account;

use App\Domain\Account\Account;
use App\Infrastructure\Persistence\PersistenceRepository;
use DateTimeImmutable;

class AcccountRepository extends PersistenceRepository
{
    public function findAll(){}

    /**
     * @throws \Exception
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM accounts WHERE id = :id";
        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

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


}