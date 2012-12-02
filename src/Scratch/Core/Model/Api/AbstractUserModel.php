<?php

namespace Scratch\Core\Model\Api;

use Scratch\Core\Library\AbstractModel;

abstract class AbstractUserModel extends AbstractModel
{
    public function createUser(array $properties)
    {
        $this->validator->setInput($properties);
        $this->validator->expect('username')->toBeUnique(function ($username) {
            return $this->isUsernameUnique($username);
        });
        $this->validator->expect('username')->toBeAlphanumeric(5, 60);
        $this->validator->expect('password')->toBeConfirmed();
        $this->validator->expect('password')->toBeAlphanumeric(5, 60);
        $this->validator->expect('firstName')->toBeString(2, 60);
        $this->validator->expect('lastName')->toBeString(2, 60);
        //$this->validator->expect('avatar')->toBeFile(1024, ['jpeg', 'png', 'gif']);
        //$this->validator->expect('platformMaskId')->toBeIn([1, 2, 3]);
        $this->validator->check();

        //return $this->doCreateUser($username, $password, $firstName, $lastName, $avatar, $platformMaskId);
        return $this->doCreateUser(
            $properties['username'],
            $properties['password'][0],
            $properties['firstName'],
            $properties['lastName'],
            1
        );
    }


    abstract public function getUserByCredentials($username, $password);

    abstract public function getUserById($id);

    abstract public function isUsernameUnique($username);

    //abstract protected function doCreateUser($username, $password, $firstName, $lastName, $avatar, $platformMaskId);
    abstract protected function doCreateUser($username, $password, $firstName, $lastName, $platformMaskId);
}