<?php

namespace Scratch\Core\Model\Api;

use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;

abstract class AbstractUserModel implements ModuleConsumerInterface
{
    protected $coreModule;
    protected $connection;
    protected $validator;

    public function __construct(CoreModule $coreModule)
    {
        $this->coreModule = $coreModule;
        $this->connection = $coreModule->getConnection();
        $this->validator = $coreModule->getValidator();
    }

    /**
     * Creates a user.
     *
     * @param array $properties
     *
     * @return integer The id of the created user
     */
    final public function createUser(array $properties)
    {
        $this->validator->setProperties($properties, ['email' => null]);
        $this->validator->expect('username')
            ->toBeAlphanumeric(5, 60)
            ->toBeUnique(function ($username) {
                return $this->isUsernameUnique($username);
            });
        $this->validator->expect('password')
            ->toBeAlphanumeric(5, 60)
            ->toBeConfirmed();
        $this->validator->expect('firstName')->toBeString(2, 60);
        $this->validator->expect('lastName')->toBeString(2, 60);
        $this->validator->expect('email')->toBeEmail();
        //$this->validator->expect('avatar')->toBeFile(1000000, ['image/png', 'image/gif', 'image/jpeg']);
        //$this->validator->expect('platformMaskId')->toBeIn([1, 2, 3]);
        $this->validator->throwViolations();

        // just a test... (not recorded in db, not nullable...)
        //rename(
          //  $this->validator->getProperty('avatar')['tmp_name'],
          //  $this->container['config']['fileDir'] . '/' . $this->validator->getProperty('avatar')['name']
        //);

        return $this->doCreateUser(
            $this->validator->getProperty('username'),
            $this->validator->getProperty('password', true),
            $this->validator->getProperty('firstName'),
            $this->validator->getProperty('lastName'),
            $this->validator->getProperty('email'),
            1
        );
    }

    /**
     * Retrieve a user by its id.
     *
     * @param string username
     * @param string password
     *
     * @return array The user properties (define!)
     */
    abstract public function getUserByCredentials($username, $password);

    /**
     * Retrieves a user by his id.
     *
     * @param integer id
     *
     * @return array The user properties (define!)
     */
    abstract public function getUserById($id);

    /**
     * Checks if a username is unique.
     *
     * @param string username
     *
     * @return boolean
     */
    abstract public function isUsernameUnique($username);

    /**
     * Creates a user
     *
     * @param string username
     * @param string password
     * @param string firstName
     * @param string lastName
     * @param string email
     * @param integer platformMaskId
     *
     * @return integer The id of the created user
     */
    abstract protected function doCreateUser($username, $password, $firstName, $lastName, $email, $platformMaskId);
}