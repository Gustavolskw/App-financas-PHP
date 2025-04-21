<?php

namespace App\Domain\Account;

use App\Domain\Account\Account;
use DateTimeImmutable;


class AccountDTO
{
    private int $id;
    private int $userId;
    private string $userEmail;
    private string $name;
    private string $description;
    private int $status;
    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

}