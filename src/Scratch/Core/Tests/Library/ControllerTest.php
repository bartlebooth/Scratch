<?php

namespace Scratch\Core\Library;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    private $controller;

    protected function setUp()
    {
        $this->controller = new Controller();
        $this->controller->setContainer((new Client())->getContainer());
    }

    public function testGetPostedDataTrimStringValues()
    {
        $_POST = [
            'foo' => 'a  ',
            'bar' => ' b',
            'baz' => [
                'bat' => '  y  '
            ]
        ];
        $expected = [
            'foo' => 'a',
            'bar' => 'b',
            'baz' => [
                'bat' => 'y'
            ]
        ];
        $this->assertEquals($expected, $this->controller->getPostedData());
    }

    public function testGetPostedDataRemoveEmptyValues()
    {
        $_POST = [
            'foo' => 'a',
            'bar' => '',
            'baz' => [
                'bam' => ' ',
                'bat' => 'b'
            ],
            'bag' => []
        ];
        $expected = [
            'foo' => 'a',
            'baz' => [
                'bat' => 'b'
            ]
        ];
        $this->assertEquals($expected, $this->controller->getPostedData());
    }
}