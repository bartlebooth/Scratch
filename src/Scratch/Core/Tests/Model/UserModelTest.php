<?php

namespace Scratch\Core\Model\Api;

use Scratch\Core\Library\Testing\Client;

class UserModelTest extends \PHPUnit_Framework_TestCase
{
    /** @var AbstractUserModel */
    private $model;
    /** @var \PDO */
    private $connection;

    protected function setUp()
    {
        $core = (new Client)->getModule('Scratch\Core\Module\CoreModule');
        $this->model = $core->getModel('Scratch/Core', 'UserModel');
        $this->connection = $core->getConnection();
        $this->connection->beginTransaction();
    }

    protected function tearDown()
    {
        $this->connection->rollback();
    }

    public function testCreateAndRetrieveUser()
    {
        $user = [
            'username' => 'admin123',
            'password' => ['admin123', 'admin123'],
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'jdoe@foo.bar',
            'platformMaskId' => '1',
        ];
        $userId = $this->model->createUser($user);
        unset($user['password']);
        $this->assertEquals($user, $this->model->getUserById($userId));
        $user['id'] = $userId;
        $this->assertEquals($user, $this->model->getUserByCredentials('admin123', 'admin123'));
        $this->assertFalse($this->model->isUsernameUnique('admin123'));
    }
}