<?php

namespace Scratch\Core\Model\Driver\MySQL;

use Scratch\Core\Model\Api\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function getUsers()
    {
        return ['user1', 'user2'];
    }
}