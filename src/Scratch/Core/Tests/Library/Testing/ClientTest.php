<?php

namespace Scratch\Core\Library\Testing;

require_once __DIR__ . '/src/FakeVendor1/Package1/Controller/Controller1.php';
require_once __DIR__ . '/src/FakeVendor1/Package2/Controller/Controller1.php';

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testClientUseMainConfigurationByDefault()
    {
        $client = new Client();
        $coreFqcn = 'Scratch\Core\Module\CoreModule';
        $this->assertInstanceOf($coreFqcn, $client->getModule($coreFqcn));
    }

    public function testNewParametersValuesCanBeMergedWithMainConfigurationParameters()
    {
        $mainConfig = require __DIR__ . '/../../../../../../config/main.php';
        $originalDevIps = $mainConfig['devIps'];
        $client = new Client(['devIps' => ['123.4.5.6' => true]]);
        $mergedConfig = $client->getModule('Scratch\Core\Module\CoreModule')->getConfiguration();
        $this->assertEquals(array_merge($originalDevIps, ['123.4.5.6' => true]), $mergedConfig['devIps']);
    }

    public function testClientCanSimulateHttpRequestOnFrontController()
    {
        $client = new Client([
            'packages' => [
                'FakeVendor1/Package1' => [
                    'isActive' => true,
                    'definitions' => __DIR__ . '/src/FakeVendor1/Package1/Resources/config/definitions.php'
                ]
            ]
        ]);
        $client->request('/prefix1/action1', 'GET');
        $this->assertEquals('FakeVendor1\Package1\Controller1::action1 output', $client->getResponse()['content']);
        $this->assertEquals(200, $client->getResponse()['code']);
    }

    public function testClientFollowsRedirectsByDefault()
    {
        $client = new Client([
            'packages' => [
                'FakeVendor1/Package1' => [
                    'isActive' => true,
                    'definitions' => __DIR__ . '/src/FakeVendor1/Package1/Resources/config/definitions.php'
                ],
                'FakeVendor1/Package2' => [
                    'isActive' => true,
                    'definitions' => __DIR__ . '/src/FakeVendor1/Package2/Resources/config/definitions.php'
                ]
            ]
        ]);
        $client->request('/prefix2/action1', 'GET');
        $this->assertEquals('FakeVendor1\Package1\Controller1::action1 output', $client->getResponse()['content']);
        $this->assertEquals(200, $client->getResponse()['code']);
    }

    public function testClientCanIgnoreRedirections()
    {
        $client = new Client([
            'packages' => [
                'FakeVendor1/Package2' => [
                    'isActive' => true,
                    'definitions' => __DIR__ . '/src/FakeVendor1/Package2/Resources/config/definitions.php'
                ]
            ]
        ]);
        $client->followRedirects(false);
        $client->request('/prefix2/action1', 'GET');
        $this->assertEquals('Redirecting to FakeVendor1\Package1\Controller1::action1...', $client->getResponse()['content']);
        $this->assertEquals(302, $client->getResponse()['code']);
    }

    public function testGetResponseThrowsAnExceptionIfNoRequestWasMade()
    {
        $this->setExpectedException('Scratch\Core\Library\Testing\Exception\UnavailableResponseException');
        $client = new Client();
        $client->getResponse();
    }
}