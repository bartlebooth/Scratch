<?php

namespace Scratch\Core\Model\Api;

interface UserModelInterface
{
    /**
     * If an user is registered with the credentials passed in, returns :
     * stdClass User {
     *     integer id,
     *     string firstName,
     *     string lastName,
     *     string username,
     * }
     * Else, returns false.
     *
     * @param string $username
     * @param string $password
     * @return object|boolean
     */
    function getUserByCredentials($username, $password);

    function getUserById($id);

    function createUser($username, $password, $firstName, $lastName, $platformMaskId);
}

abstract class Test
{
    public function createUser(array $properties)
    {
        $this->validator->setInput($properties);
        $this->validator->expect('username')->toBeAlphanumeric(6, 60);
        $this->validator->expect('password')->toBeConfirmed();
        $this->validator->expect('password')->toBeAlphanumeric(6, 60);
        $this->validator->expect('firstName')->toBeString(2, 60);
        $this->validator->expect('lastName')->toBeString(2, 60);
        $this->validator->expect('avatar')->toBeFile(1024, ['jpeg', 'png', 'gif']);
        $this->validator->expect('platformMaskId')->toBeIn([1, 2, 3]);
        $this->validator->check();

        return $this->doCreateUser($username, $password, $firstName, $lastName, $avatar, $platformMaskId);
    }

    abstract public function getUserByCredentials($username, $password);

    abstract public function getUserById($id);

    abstract protected function doCreateUser($username, $password, $firstName, $lastName, $avatar, $platformMaskId);
}