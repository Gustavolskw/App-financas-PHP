<?php

namespace Auth\DTO;

use Auth\Entity\User;

class UserDTO
{
    private int $id;
    private string $name;
    private string $email;
    private string|int $role;
    private int $status;

    public function __construct(int $id, string $name, string $email, $role, $status)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'status' => boolval($this->status),
        ];
    }

    public static function fromArray(User $user): self
    {
        return new self($user->getId(), $user->getName(), $user->getEmail(), $user->getRole(), $user->getStatus());
    }
}