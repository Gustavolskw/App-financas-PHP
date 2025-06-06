<?php

namespace App\Domain\Entity;

class Icon
{

    private int $id;
    private string $name;
    private string $color;
    private string $iconFile;
    private bool $satus;

    public function __construct(int $id, string $name, string $color, string $iconFile, bool $satus = true)
    {
        $this->id = $id;
        $this->name = $name;
        $this->color = $color;
        $this->iconFile = $iconFile;
        $this->satus = $satus;
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

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): void
    {
        $this->color = $color;
    }

    public function getIconFile(): string
    {
        return $this->iconFile;
    }

    public function setIconFile(string $iconFile): void
    {
        $this->iconFile = $iconFile;
    }

    public function isSatus(): bool
    {
        return $this->satus;
    }

    public function setSatus(bool $satus): void
    {
        $this->satus = $satus;
    }
}
