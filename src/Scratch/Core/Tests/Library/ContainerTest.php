<?php

namespace Scratch\Core\Library;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $container;

    protected function setUp()
    {
        $this->client = new Client();
        $this->container = $this->client->getContainer();
    }

    public function test()
    {
        $this->assertFalse($this->container['match']('/non-existent-url/123', 'GET', false));
        $this->assertTrue($this->container['match']('/', 'GET', false));
        $this->assertTrue($this->container['match']('/prefixFoo/index', 'GET', false));
    }

    public function test2()
    {
        $this->setExpectedException('RuntimeException');
        $this->client->request('/gsfgsfdg', 'gfgs');
    }

    public function test3()
    {
        $response = $this->client->request('/', 'GET');
        $this->assertContains('Accueil', $response['body']);
        $this->assertEquals(200, $response['code']);
    }
}