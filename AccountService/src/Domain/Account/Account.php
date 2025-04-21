<?php

declare(strict_types=1);

namespace App\Domain\Account;

use DateTimeImmutable;

class Account
{

    private ?int $id;
    private int $userId;
    private string $userEmail;
    private string $name;
    private string $description;
    private bool $status;
    private ?DateTimeImmutable $createdAt;
    private ?DateTimeImmutable $updatedAt;


    public function __construct(
        ?int $id,
        int $userId,
        string $userEmail,
        string $name,
        string $description,
        bool $status,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }


    public function toString():string
    {
        return "Account [id={$this->id},\n
         userId={$this->userId},\n
         userEmail={$this->userEmail},\n 
         name={$this->name},\n
         description={$this->description},\n
         status={$this->status},\n
         createdAt={$this->createdAt?->format('Y-m-d H:i:s')},\n
         updatedAt={$this->updatedAt?->format('Y-m-d H:i:s')}]";
    }
}
