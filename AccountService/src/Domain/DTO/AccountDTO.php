<?php

namespace App\Domain\DTO;

use App\Domain\Entity\Account;
use DateTimeImmutable;

class AccountDTO
{
    private int $id;
    private int $userId;
    private string $userEmail;
    private string $name;
    private string $description;
    private bool $status;
    private ?DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;


    public function __construct(Account $account)
    {
        $this->id = $account->getId();
        $this->userId = $account->getUserId();
        $this->userEmail = $account->getUserEmail();
        $this->name = $account->getName();
        $this->description = $account->getDescription();
        $this->status = $account->getStatus();
        $this->createdAt = $account->getCreatedAt() ?? null;
        $this->updatedAt = $account->getUpdatedAt() ?? null;
    }
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'userEmail' => $this->userEmail,
            'name' =>$this->name,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
