<?php

namespace Acc\DTO;

class AccountDTO
{
    public int $id;
    public int $userId;
    public string $userEmail;
    public string $name;
    public string $description;
    public int $status;
    public string $created_at;
    public string $updated_at;

    public function __construct(int $id, int $userId, string $userEmail, string $name, string $description, $status, $created_at, $updated_at)
    {
        $this->id = $id;
        $this->userId = $userId;
        $this->userEmail = $userEmail;
        $this->name = $name;
        $this->description = $description;
        $this->status = $status;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'userEmail' => $this->userEmail,
            'name' => $this->name,
            'description' => $this->description,
            'status' => boolval($this->status),
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self($data['id'], $data['userId'], $data['userEmail'], $data['name'], $data['description'], $data['status'], $data['created_at'], $data['updated_at']);
    }
}