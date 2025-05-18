<?php

namespace Application\UseCases;

use App\Application\Handlers\ServiceHttpHandler;
use App\Application\UseCases\Account\CreateAccountCase;
use App\Domain\Entity\Account;
use App\Domain\Exception\InvalidUserException;
use App\Infrastructure\Persistence\Account\PdoAccountRepository;
use DI\DependencyException;
use DI\NotFoundException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use Psr\Log\LoggerInterface;
use Tests\TestCase;
use DI\Container;

class CreateAccountTest extends TestCase
{
    public static function accountProvider(): array
    {
        return [
            [
                1, // id
                3, // userId
                "gustavo@email.com", // userEmail
                "Test Case", // name
                "Test Case Development", // description
                true, // status
                new \DateTimeImmutable('2025-01-01 00:00:00'), // createdAt
                new \DateTimeImmutable('2025-01-02 00:00:00')  // updatedAt
            ]
        ];
    }

    public function setUp(): void
    {

    }

    /**
     * @throws Exception
     * @throws InvalidUserException
     * @throws GuzzleException
     */
    #[DataProvider('accountProvider')]
    public function testCreateAccountWithValidation(int $id, int $userId, string $userEmail, string $name, string $description, bool $status, \DateTimeImmutable $createdAt, \DateTimeImmutable $updatedAt): void
    {
        $account = new Account($id, $userId, $userEmail, $name, $description, $status, $createdAt, $updatedAt);
        $httpHandlerMock = $this->createMock(ServiceHttpHandler::class);
        $httpHandlerMock->expects($this->once())
            ->method('handleUserValidationRequest')
            ->willReturn(true); // simula sucesso
        $accountPdoRepository = $this->createMock(PdoAccountRepository::class);
        $accountPdoRepository->expects($this->once())->method('save')->willReturn($account);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $createAccountCase = new CreateAccountCase($loggerMock, $accountPdoRepository, $httpHandlerMock);
        $accountData = [
            'userId' => $userId,
            'userEmail' => $userEmail,
            'name' => $name,
            'description' => $description,
        ];
        $result = $createAccountCase->execute($accountData);
        $this->assertEquals($id, $result->toArray()['id']);
        $this->assertEquals($userId, $result->toArray()['userId']);
        $this->assertEquals($userEmail, $result->toArray()['userEmail']);
        $this->assertEquals($name, $result->toArray()['name']);
        $this->assertEquals($description, $result->toArray()['description']);
    }

    /**
     * @throws Exception
     * @throws InvalidUserException
     * @throws NotFoundException
     * @throws GuzzleException
     * @throws DependencyException
     * @throws \Exception
     */
    #[DataProvider('accountProvider')]
    public function testCreateAccountWithNoValidation(int $id, int $userId, string $userEmail, string $name, string $description, bool $status, \DateTimeImmutable $createdAt, \DateTimeImmutable $updatedAt): void
    {
        $account = new Account($id, $userId, $userEmail, $name, $description, $status, $createdAt, $updatedAt);
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $accountPdoRepository = $this->createMock(PdoAccountRepository::class);
        $accountPdoRepository->expects($this->once())->method('save')->willReturn($account);
        $createAccountCase = new CreateAccountCase($container->get(LoggerInterface::class), $accountPdoRepository, new ServiceHttpHandler($container->get(LoggerInterface::class)));
        $accountData = [
            'userId' => $userId,
            'userEmail' => $userEmail,
            'name' => $name,
            'description' => $description,
        ];
        $result = $createAccountCase->execute($accountData, true);
        $this->assertIsArray($result->toArray());
        $this->assertEquals($result->toArray()['id'], $id);
        $this->assertEquals($result->toArray()['userId'], $userId);
        $this->assertEquals($result->toArray()['userEmail'], $userEmail);
        $this->assertEquals($result->toArray()['name'], $name);
        $this->assertEquals($result->toArray()['description'], $description);
    }

    /**
     * @throws Exception
     * @throws InvalidUserException
     * @throws GuzzleException
     */
    #[DataProvider('accountProvider')]
    public function testCreateAccountWithErrorOnValidation(int $id, int $userId, string $userEmail, string $name, string $description, bool $status, \DateTimeImmutable $createdAt, \DateTimeImmutable $updatedAt): void
    {
        $httpHandlerMock = $this->createMock(ServiceHttpHandler::class);
        $httpHandlerMock->expects($this->once())
            ->method('handleUserValidationRequest')
            ->willReturn(false);
        $accountPdoRepository = $this->createMock(PdoAccountRepository::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $createAccountCase = new CreateAccountCase($loggerMock, $accountPdoRepository, $httpHandlerMock);
        $accountData = [
            'userId' => $userId,
            'userEmail' => $userEmail,
            'name' => $name,
            'description' => $description,
        ];
        $this->expectException(InvalidUserException::class);
        $this->expectExceptionMessage("User not found");
        $createAccountCase->execute($accountData);
    }

}