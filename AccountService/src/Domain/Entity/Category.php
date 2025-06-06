<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use DateTimeInterface;

class Category
{
    private int $id;
    private string $name;
    private bool $type;
    private Icon $icon;
    private bool $status;
    private DateTimeInterface $createdAt;
    private DateTimeInterface $updatedAt;


    public function __construct(int $id, string $name, bool $type, Icon $icon, bool $status = true, DateTimeInterface|null $createdAt, DateTimeInterface|null $updatedAt)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->icon = $icon;
        $this->status = $status;
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isType(): bool
    {
        return $this->type;
    }

    public function setType(bool $type): void
    {
        $this->type = $type;
    }

    public function getIcon(): Icon
    {
        return $this->icon;
    }

    public function setIcon(Icon $icon): void
    {
        $this->icon = $icon;
    }

    public function isStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
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