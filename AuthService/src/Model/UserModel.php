<?php

namespace Auth\Model;

use Auth\Entity\User;
use Auth\Config\Database;
use PDO;

class UserModel
{
    private $db;

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


    public function findByEmail(string $email):?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $userArray = $stmt->fetch(PDO::FETCH_ASSOC);
        if($userArray){
            return new User($userArray['id'], $userArray['name'], $userArray['email'], $userArray['password'], $userArray['role'], $userArray['status']);
        }
        return null;
    }

     public function create(User $user):?User
    {
        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, status) VALUES (:name, :email, :password, :role, :status)");

            $username = $user->getName();
            $userEmail = $user->getEmail();
            $userPassword = $user->getPassword();
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
                throw new \Exception('Falha ao inserir usuário.');
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            return null;
        }
    }

    // Atualizar os dados de um usuário
    public function update(User $user)
    {
        $stmt = $this->db->prepare("UPDATE users SET name = :name, email = :email, password = :password, role = :role, status = :status WHERE id = :id");


        $username = $user->getName();
        $userEmail = $user->getEmail();
        $userPassword = $user->getPassword();
        $userRole = $user->getRole();
        $userStatus = $user->getStatus();

        $userId = $user->getId();


        $stmt->bindParam(':name', $username);
        $stmt->bindParam(':email', $userEmail);
        $stmt->bindParam(':password', $userPassword);
        $stmt->bindParam(':role', $userRole);
        $stmt->bindParam(':status', $userStatus);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function userExistsByEmail(string $email):bool
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
}