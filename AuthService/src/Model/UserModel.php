<?php

namespace Auth\Model;

use Auth\Entity\User;
use Auth\Config\Database;
use Exception;
use PDO;

class UserModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPDO();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            // Retorna uma instância de User populada com os dados
            return new User($result['id'], $result['name'], $result['email'], $result['password'], $result['role'], $result['status']);
        }
        return null;
    }

    public function findAll(): mixed
    {
        $slqQuery = "SELECT * FROM users";
        $stmt = $this->db->prepare($slqQuery);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $users = [];

        foreach ($rows as $row) {
            $user = new User(
                $row['id'] ?? null,
                $row['name'] ?? null,
                $row['email'] ?? null,
                $row['password'] ?? null,
                $row['role'] ?? null,
                $row['status'] ?? null,
            );

            $users[] = $user;
        }

        return $users;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email LIKE :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $userArray = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userArray) {
            return new User($userArray['id'], $userArray['name'], $userArray['email'], $userArray['password'], $userArray['role'], $userArray['status'], $userArray['created_at'], $userArray['updated_at']);
        }
        return null;
    }

    public function create(User $user): ?User
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, :role, :status)");

            $username = $user->getName();
            $userEmail = $user->getEmail();
            $userPassword = password_hash($user->getPassword(), PASSWORD_BCRYPT);
            $userRole = $user->getRole();
            $userStatus = $user->getStatus();

            $stmt->bindParam(':name', $username);
            $stmt->bindParam(':email', $userEmail);
            $stmt->bindParam(':password', $userPassword);
            $stmt->bindParam(':role', $userRole);
            $stmt->bindParam(':status', $userStatus);


            if ($stmt->execute()) {

                $user->setId($this->db->lastInsertId());

                $this->db->commit();

                return $user;
            } else {
                throw new Exception('Falha ao inserir usuário.');
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function update(User $user, int $id)
    {
        $this->db->beginTransaction();
        try {

            $fieldMap = [
                'name' => 'getName',
                'email' => 'getEmail',
                'password' => 'getPassword',
                'role' => 'getRole',
                'status' => 'getStatus',
            ];

            $setClauses = [];
            $params = [];

            foreach ($fieldMap as $column => $getter) {
                $value = $user->$getter();
                if ($value !== null) {
                    $setClauses[] = "$column = :$column";
                    $params[":$column"] = $value;
                }
            }

            if (empty($setClauses)) {
                return null;
            }

            $params[':id'] = $id;
            $setSQL = implode(', ', $setClauses);
            $sql = "UPDATE users SET $setSQL WHERE id = :id";

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($stmt->execute()) {
                $this->db->commit();
                return $user;
            }

            throw new Exception("Error while updating user");
        } catch (Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    public function remove($id)
    {
        $stmt = $this->db->prepare("UPDATE users SET status = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function userExistsByEmail(string $email): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            return true;
        }
        return false;
    }

    public function findByIdAndEmail(int $id, string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE id = :id AND email LIKE :email";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $userArray = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userArray) {
            return new User($userArray['id'], $userArray['name'], $userArray['email'], $userArray['password'], $userArray['role'], $userArray['status']);
        }
        return null;
    }
}