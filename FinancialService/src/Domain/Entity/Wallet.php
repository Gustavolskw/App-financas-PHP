<?php

namespace App\Domain\Entity;

use JsonSerializable;

class Wallet implements JsonSerializable
{
    private ?int $id;
    private int $userId;
    private string $userEmail;
    private string $name;
    private ?string $description;
    private bool $status;
    private \DateTimeInterface $createdAt;

    public function __construct(
        ?int $id,
        int $userId,
        string $userEmail,
        string $name,
        ?string $description,
        bool $status,
        \DateTimeInterface $createdAt
    ) {
        $this->id = $id ?? null;
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getUserEmail(): string
    {
        return $this->userEmail;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'userEmail' => $this->userEmail,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }

}