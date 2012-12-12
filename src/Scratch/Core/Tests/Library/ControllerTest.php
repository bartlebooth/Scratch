<?php

namespace Scratch\Core\Library;

class ControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterTrimStringValues()
    {
        $controller = new Controller();
        $data = $controller->filter([
            'foo' => 'a  ',
            'bar' => ' b',
            'baz' => [
                'bat' => '  y  '
            ]
        ]);
        $expected = [
            'foo' => 'a',
            'bar' => 'b',
            'baz' => [
                'bat' => 'y'
            ]
        ];
        $this->assertEquals($expected, $data);
    }

    public function testFilterRemoveEmptyValues()
    {
        $controller = new Controller();
        $data = $controller->filter([
            'foo' => 'a',
            'bar' => '',
            'baz' => [
                'bam' => ' ',
                'bat' => 'b'
            ],
            'bag' => []
        ]);
        $expected = [
            'foo' => 'a',
            'baz' => [
                'bat' => 'b'
            ]
        ];
        $this->assertEquals($expected, $data);
    }
}