<?php

namespace App\Domain\Entity;

class Wallet
{

    private int $id;
    private int $userId;
    private string $userEmail;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;

    public function __construct(int $id, int $userId, string $userEmail, DateTimeInterface $createdAt, DateTimeInterface $updatedAt)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }
    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function setUserEmail(string $userEmail): void
    {
        $this->userEmail = $userEmail;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }


}