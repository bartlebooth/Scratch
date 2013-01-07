<?php

namespace Scratch\Core\Library\Testing;

require_once __DIR__ . '/src/FakeVendor1/Package1/Controller/Controller1.php';
require_once __DIR__ . '/src/FakeVendor1/Package2/Controller/Controller1.php';
require_once __DIR__ . '/src/FakeVendor1/Package3/Controller/Controller1.php';
require_once __DIR__ . '/src/FakeVendor2/Package1/Controller/Controller1.php';

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
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package1']);
        $client->request('/prefix1/action1', 'GET');
        $this->assertEquals('FakeVendor1\Package1\Controller1::action1 output', $client->getResponse()['content']);
        $this->assertEquals(200, $client->getResponse()['code']);
    }

    public function testClientFollowsRedirectsByDefault()
    {
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package1', 'FakeVendor1/Package2']);
        $client->request('/prefix2/action1', 'GET');
        $this->assertEquals('FakeVendor1\Package1\Controller1::action1 output', $client->getResponse()['content']);
        $this->assertEquals(200, $client->getResponse()['code']);
    }

    public function testClientCanIgnoreRedirections()
    {
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package2']);
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

    public function testClientCanPerformXPathQueriesOnResponse()
    {
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package2']);
        $client->request('/prefix2/action2', 'GET');
        $title = $client->xPathQuery('/html/body/h1');
        $this->assertEquals(1, $title->length);
        $this->assertEquals('Foo', $title->item(0)->nodeValue);
        $barDiv = $client->xPathQuery('//div[@id="bar"]');
        $this->assertEquals(1, $barDiv->length);
        $spans = $client->xPathQuery('./span', $barDiv->item(0));
        $this->assertEquals(2, $spans->length);
    }

    public function testClientRefreshesXPathBetweenRequests()
    {
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package1', 'FakeVendor1/Package2']);
        $client->request('/prefix1/action2', 'GET');
        $this->assertEquals(1, $client->xPathQuery('//div[@id="bar"]/span')->length);
        $client->request('/prefix2/action2', 'GET');
        $this->assertEquals(2, $client->xPathQuery('//div[@id="bar"]/span')->length);
    }

    public function testClientCanClickOnResponseLinks()
    {
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package1', 'FakeVendor1/Package3']);
        $client->request('/prefix3/action1', 'GET');
        $client->clickLink('//a');
        $this->assertEquals('Output of /prefix3/action2 (link target of /prefix3/action1)', $client->getResponse()['content']);
    }

    public function testClickLinkThrowsAnExceptionIfNodeListIsEmpty()
    {
        $this->setExpectedException('Scratch\Core\Library\Testing\Exception\EmptyNodeListException');
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package3']);
        $client->request('/prefix3/action3', 'GET');
        $client->clickLink('//div[@id="non-existent-div"]/a');
    }

    public function testClickLinkThrowsAnExceptionIfSelectedNodeIsNotAnAnchor()
    {
        $this->setExpectedException('Scratch\Core\Library\Testing\Exception\UnexpectedTagNameException');
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package3']);
        $client->request('/prefix3/action4', 'GET');
        $client->clickLink('//div');
    }

    public function testClickLinkThrowsAnExceptionIfSelectedAnchorHasNoHrefAttribute()
    {
        $this->setExpectedException('Scratch\Core\Library\Testing\Exception\MissingAttributeException');
        $client = $this->initClientWithTestPackages(['FakeVendor1/Package3']);
        $client->request('/prefix3/action5', 'GET');
        $client->clickLink('/div/a[@id="no-href-anchor"]');
    }

    public function testClientCanSubmitForm()
    {
        $client = $this->initClientWithTestPackages(['FakeVendor2/Package1']);
        $client->request('/prefix4/action1', 'GET');
        $this->assertTrue(false);
    }

    public function testSubmitFormThrowsAnExceptionIfNodeListIsEmpty()
    {
        $this->setExpectedException('Scratch\Core\Library\Testing\Exception\EmptyNodeListException');
    }

    public function testSubmitFormThrowsAnExceptionIfSelectedNodeIsNotAForm()
    {
        $this->setExpectedException('Scratch\Core\Library\Testing\Exception\UnexpectedTagNameException');
    }

    public function testSubmitFormThrowsAnExceptionIfSelectedFormHasNoActionAttribute()
    {
        $this->setExpectedException('Scratch\Core\Library\Testing\Exception\MissingAttributeException');
    }

    private function initClientWithTestPackages(array $packages)
    {
        $availablePackages = [
            'FakeVendor1/Package1' => [
                'isActive' => true,
                'definitions' => __DIR__ . '/src/FakeVendor1/Package1/Resources/config/definitions.php'
            ],
            'FakeVendor1/Package2' => [
                'isActive' => true,
                'definitions' => __DIR__ . '/src/FakeVendor1/Package2/Resources/config/definitions.php'
            ],
            'FakeVendor1/Package3' => [
                'isActive' => true,
                'definitions' => __DIR__ . '/src/FakeVendor1/Package3/Resources/config/definitions.php'
            ],
            'FakeVendor2/Package1' => [
                'isActive' => true,
                'definitions' => __DIR__ . '/src/FakeVendor2/Package1/Resources/config/definitions.php'
            ]
        ];
        $clientPackages = [];

        foreach ($packages as $package) {
            $clientPackages[$package] = $availablePackages[$package];
        }

        return new Client(['packages' => $clientPackages]);
    }
}