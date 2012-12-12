<?php

namespace Scratch\Core\Model\Driver\MySQL;

use \PDO;
use Scratch\Core\Model\Api\AbstractUserModel;

class UserModel extends AbstractUserModel
{
    public function getUserByCredentials($username, $password)
    {
        $stmt = $this->connection->prepare('
            SELECT `id`, `username`, `firstName`, `lastName`
            FROM `users`
            WHERE `username` = ?
            AND `password` = SHA2(?, 512)
        ');
        $stmt->execute([$username, $password]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function getUserById($id)
    {
        $stmt = $this->connection->prepare('
            SELECT `id`, `username`, `firstName`, `lastName`
            FROM `users`
            WHERE `id` = ?
        ');
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function isUsernameUnique($username)
    {
        $stmt = $this->connection->prepare('
            SELECT `id`
            FROM `users`
            WHERE `username` = ?
        ');
        $stmt->execute([$username]);

        return false === $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function doCreateUser($username, $password, $firstName, $lastName, $email, $platformMaskId)
    {
        $stmt = $this->connection->prepare('
            INSERT `users` (`username`, `password`, `firstName`, `lastName`, `email`, `platformMaskId`)
            VALUES (?, SHA2(?, 512), ?, ?, ?, ?)
        ');
        $stmt->bindValue(1, $username, PDO::PARAM_STR);
        $stmt->bindValue(2, $password, PDO::PARAM_STR);
        $stmt->bindValue(3, $firstName, PDO::PARAM_STR);
        $stmt->bindValue(4, $lastName, PDO::PARAM_STR);
        $stmt->bindValue(5, $email, PDO::PARAM_STR);
        $stmt->bindValue(6, $platformMaskId, PDO::PARAM_INT);

        return !$stmt->execute() ?: $this->connection->lastInsertId();
    }
}