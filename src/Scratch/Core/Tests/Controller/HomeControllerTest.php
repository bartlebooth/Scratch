<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Testing\Client;

class HomeControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testHomeIndex()
    {
        $client = new Client();
        $response = $client->request('/', 'GET');
        $this->assertContains('Accueil', $response['body']);
        $this->assertEquals(200, $response['code']);

        $client->getModule('Scratch\Core\Module\CoreModule')->destroySession();
    }
}