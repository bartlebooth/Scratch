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
}

abstract class Test
{
    public function getUserByCredentials($username, $password)
    {
        $this->validator->expect($username)->toBeAlphanumeric(6, 60);
        $this->validator->expect($password)->toBeAlphanumeric(6, 60);

        return $this->doGetUserByCredentials($username, $password);
    }

    abstract protected function doGetUserByCredentials($username, $password);
}