<?php

namespace Scratch\Core\Library\Testing;

use \DOMDocument;
use \DOMXPath;
use \DOMNode;
use Scratch\Core\Library\Module\ModuleManager;
use Scratch\Core\Library\Testing\Exception\UnavailableResponseException;
use Scratch\Core\Library\Testing\Exception\EmptyNodeListException;
use Scratch\Core\Library\Testing\Exception\UnexpectedTagNameException;
use Scratch\Core\Library\Testing\Exception\MissingAttributeException;

class Client
{
    /** @var ModuleManager */
    private $moduleManager;

    private $response;

    private $xPath;

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
        $this->xPath = null;

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

    public function xPathQuery($expression, DOMNode $node = null)
    {
        if (!$this->xPath instanceof DOMXPath) {
            $doc = new DOMDocument();
            $doc->loadXML($this->getResponse()['content']);
            $this->xPath = new DOMXPath($doc);
        }

        return $this->xPath->query($expression, $node);
    }

    public function clickLink($xPathSelector)
    {
        $linkNode = $this->checkNode($xPathSelector, 'a', 'href');

        return $this->request($linkNode->getAttribute('href'), 'GET');
    }

    public function submitForm($xPathSelector, array $post = [])
    {
        $formNode = $this->checkNode($xPathSelector, 'form', 'action');

        /*
        foreach ($post as $field => $value) {
            if ($xPath->query(".//input[@name='{$field}']", $form)->length === 0) {
                throw new \Exception("Field '{$field}' doesn't exist");
            }

            // check field is not disabled...
        }*/

        $_POST = $post;

        return $this->request($formNode->getAttribute('action'), 'POST');
    }

    public function getModule($moduleFqcn)
    {
        return $this->moduleManager->getModule($moduleFqcn);
    }

    private function checkNode($xPathSelector, $expectedTagName, $expectedAttribute)
    {
        $nodeList = $this->xPathQuery($xPathSelector);

        if ($nodeList->length === 0) {
            throw new EmptyNodeListException("The expression '{$xPathSelector}' doesn't match any node");
        }

        $node = $nodeList->item(0);

        if ($node->nodeName !== $expectedTagName) {
            throw new UnexpectedTagNameException("The node targeted by the expression '{$xPathSelector}' is not an '{$expectedTagName}' element");
        }

        if (!$node->hasAttribute($expectedAttribute)) {
            throw new MissingAttributeException("The '{$expectedTagName}' element targeted by the expression '{$xPathSelector}' has no '{$expectedAttribute}' attribute");
        }

        return $node;
    }
}