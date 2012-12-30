<?php

namespace Scratch\Core\Library;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    private $controller;

    protected function setUp()
    {
        $this->markTestSkipped('Need to find a way to inject request error');
        $this->controller = new Controller();
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
}