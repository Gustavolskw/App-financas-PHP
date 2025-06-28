<?php

namespace App\Domain\Utils;

use App\Domain\Entity\Wallet;

trait DomainObjectMapper
{

    public function buildWallet(array $data): Wallet
    {
        return new Wallet(
            $data['id'] ?? null,
            $data['user_id'] ?? null,
            $data['user_email'] ?? null,
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['status'] ?? null,
            $data['createdAt'] ?? new \DateTimeImmutable(),
        );
    }
}