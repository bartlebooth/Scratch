<?php

namespace Scratch\Core\Library\Testing;

use Scratch\Core\Library\Module\ModuleManager;
use Scratch\Core\Library\Testing\Exception\UnavailableResponseException;

class Client
{
    /** @var ModuleManager */
    private $moduleManager;

    private $response;

    private $followRedirects = true;

    public function __construct(array $config = null)
    {
        $config = $config ? array_merge_recursive(require __DIR__ . '/../../../../../config/main.php', $config) : $config;
        $env = 'test';
        $matchUrl = false;
        $autoload = false;
        $this->moduleManager = require __DIR__ . '/../../../../../public/index.php';
    }

    public function followRedirects($followRedirects)
    {
        $this->followRedirects = $followRedirects;
    }

    public function request($pathInfo, $method)
    {
        ob_start();
        $this->moduleManager->getModule('Scratch\Core\Module\CoreModule')->matchUrl($pathInfo, $method);
        $this->response = [
            'headers' => xdebug_get_headers(),
            'code' => http_response_code(),
            'content' => ob_get_clean()
        ];
        $_POST = [];
        $_GET = [];
        $_FILES = [];

        if ($this->followRedirects) {
            foreach ($this->response['headers'] as $header) {
                if (preg_match('# *Location: *(.*)#', $header, $matches)) {
                    $this->response = $this->request(empty($matches[1]) ? '/' : $matches[1], 'GET');
                    break;
                }
            }
        }

        return $this->response;
    }

    public function getResponse()
    {
        if (!isset($this->response)) {
            throw new UnavailableResponseException('No response is available : a request must be made first');
        }

        return $this->response;
    }

    public function submitForm($formId, array $post= [])
    {
        $doc = new \DOMDocument();
        $doc->loadXML($this->getResponse()['content']);
        $xPath = new \DOMXPath($doc);
        $form = $xPath->query("//form[@id='{$formId}']")->item(0);
        $action = $form->getAttribute('action');

        foreach ($post as $field => $value) {
            if ($xPath->query(".//input[@name='{$field}']", $form)->length === 0) {
                throw new \Exception("Field '{$field}' doesn't exist");
            }

            // check field is not disabled...
        }

        $_POST = $post; // reinit superglobals between requests...

        return $this->response = $this->request($action, 'POST');
    }

    public function getModule($moduleFqcn)
    {
        return $this->moduleManager->getModule($moduleFqcn);
    }
}