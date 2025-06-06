<?php

namespace App\Domain\Entity;

use DateTimeImmutable;

class MonthlyBudget
{
    private int $id;
    private Wallet $wallet;
    private Category $category;
    private int|float $budgedAmount;
    private int $month;
    private int $year;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;


    public function __construct(
        int $id,
        Wallet $wallet,
        Category $category,
        int|float $budgedAmount,
        int $month,
        int $year,
        DateTimeImmutable|null $createdAt,
        DateTimeImmutable|null $updatedAt
    ) {
        $this->id = $id;
        $this->wallet = $wallet;
        $this->category = $category;
        $this->budgedAmount = $budgedAmount;
        $this->month = $month;
        $this->year = $year;
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

    public function getBudgedAmount(): float|int
    {
        return $this->budgedAmount;
    }

    public function setBudgedAmount(float|int $budgedAmount): void
    {
        $this->budgedAmount = $budgedAmount;
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function setMonth(int $month): void
    {
        $this->month = $month;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
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
