<?php

namespace App\Domain\Entity;

use DateTime;
use DateTimeImmutable;
use Money\Money;

class Transaction
{
    private int $id;
    private Wallet $wallet;
    private Category $category;
    private string $description;
    private bool $type;
    private float|int $amount;
    private bool $isIncome;
    private DateTimeImmutable $transactionDate;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $id,
        Wallet $wallet,
        Category $category,
        string $description,
        bool $type,
        float|int $amount,
        bool $isIncome,
        DateTimeImmutable|null $transactionDate,
        DateTimeImmutable|null $createdAt,
        DateTimeImmutable|null $updatedAt
    ) {
        $this->id = $id;
        $this->wallet = $wallet;
        $this->category = $category;
        $this->description = $description;
        $this->type = $type;
        $this->amount = $amount;
        $this->isIncome = $isIncome;
        $this->transactionDate = $transactionDate ?? new DateTimeImmutable();
        $this->createdAt = $createdAt ?? new DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getWallet(): Wallet
    {
        return $this->wallet;
    }

    public function setWallet(Wallet $wallet): void
    {
        $this->wallet = $wallet;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function isType(): bool
    {
        return $this->type;
    }

    public function setType(bool $type): void
    {
        $this->type = $type;
    }

    public function getAmount(): float|int
    {
        return $this->amount;
    }

    public function setAmount(float|int $amount): void
    {
        $this->amount = $amount;
    }

    public function isIncome(): bool
    {
        return $this->isIncome;
    }

    public function setIsIncome(bool $isIncome): void
    {
        $this->isIncome = $isIncome;
    }

    public function getTransactionDate(): DateTimeImmutable
    {
        return $this->transactionDate;
    }

    public function setTransactionDate(DateTimeImmutable $transactionDate): void
    {
        $this->transactionDate = $transactionDate;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}