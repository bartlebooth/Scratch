<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Testing\Client;

class AuthenticationControllerTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $connection;

    protected function setUp()
    {
        $this->client = new Client();
        $this->connection = $this->client->getModule('Scratch\Core\Module\CoreModule')->getConnection();
        $this->connection->beginTransaction();
    }

    protected function tearDown()
    {
        $this->connection->rollback();
    }

    public function testFailedAuthentication()
    {
        $this->client->request('/login/form', 'GET');
        $this->client->submitForm('//form[@id="login-form"]', ['username' => 'foo', 'password' => 'bar']);
        $this->assertContains('Authentication has failed', $this->client->getResponse()['content']);
    }

    public function testSuccessfulAuthentication()
    {
        $this->client->getModule('Scratch\Core\Module\CoreModule')
            ->getModel('Scratch/Core', 'UserModel')
            ->createUser(['username' => 'admin', 'password' => ['admin', 'admin'], 'firstName' => 'John', 'lastName' => 'Doe']);
        $this->client->request('/login/form', 'GET');
        $this->client->submitForm('//form[@id="login-form"]', ['username' => 'admin', 'password' => 'admin']);
        $this->assertContains('LOGGED', $this->client->getResponse()['content']);
        $this->client->request('/logout', 'GET');
        $this->assertContains('Accueil', $this->client->getResponse()['content']);
    }
}