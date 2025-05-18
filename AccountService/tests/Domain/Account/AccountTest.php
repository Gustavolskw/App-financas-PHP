<?php

namespace Domain\Account;

use App\Domain\Entity\Account;
use DateTimeImmutable;
use Tests\TestCase;

class AccountTest extends TestCase
{
 public static function accountProvider(): array
{
    return [
        [
            1, // id
            1, // userId
            "TestCase@email.com", // userEmail
            "Test Case", // name
            "Test Case Development", // description
            true, // status
            new DateTimeImmutable('2025-01-01 00:00:00'), // createdAt
            new DateTimeImmutable('2025-01-02 00:00:00')  // updatedAt
        ]
    ];
}

    /**
     * @dataProvider accountProvider
     * @param int $id
     * @param int $userId
     * @param string $userEmail
     * @param string $name
     * @param string $description
     * @param bool $status
     * @param DateTimeImmutable $createdAt
     * @param DateTimeImmutable $updatedAt
     */
    public function testCreateAccount(int $id, int $userId, string $userEmail, string $name, string $description, bool $status, DateTimeImmutable $createdAt, DateTimeImmutable $updatedAt ):void
    {
        $account = new Account($id, $userId, $userEmail, $name, $description, $status, $createdAt, $updatedAt);

        $this->assertEquals($id, $account->getId());
        $this->assertEquals($userId, $account->getUserId());
        $this->assertEquals($userEmail, $account->getUserEmail());
        $this->assertEquals($name, $account->getName());
        $this->assertEquals($description, $account->getDescription());
        $this->assertEquals($status, $account->getStatus());
        $this->assertEquals($createdAt, $account->getCreatedAt());
        $this->assertEquals($updatedAt, $account->getUpdatedAt());

    }

}