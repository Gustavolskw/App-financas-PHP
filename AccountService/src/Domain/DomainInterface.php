<?php

namespace App\Domain;

interface DomainInterface
{

    public function setId(?int $id): void;
    public function jsonSerialize(): array;
}