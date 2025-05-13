<?php

namespace App\Domain\Entity;

use DateTime;
use Money\Money;

class Transaction
{
    private int $id;
    private Account $account;
    private bool $type;
    private Category $category;
    private Money $amount;
    private DateTime $date;

    /**
     * @param int $id
     * @param Account $account
     * @param bool $type
     * @param Category $category
     * @param Money $amount
     * @param DateTime $date
     */
    public function __construct(int $id, Account $account, bool $type, Category $category, Money $amount, DateTime $date)
    {
        $this->id = $id;
        $this->account = $account;
        $this->type = $type;
        $this->category = $category;
        $this->amount = $amount;
        $this->date = $date;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAccount(): Account
    {
        return $this->account;
    }

    public function setAccount(Account $account): void
    {
        $this->account = $account;
    }

    public function isType(): bool
    {
        return $this->type;
    }

    public function setType(bool $type): void
    {
        $this->type = $type;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function setAmount(Money $amount): void
    {
        $this->amount = $amount;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): void
    {
        $this->date = $date;
    }
}