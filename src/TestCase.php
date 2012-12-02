<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class TestCase
{
    public function run()
    {
        foreach (get_class_methods($this) as $method) {
            if (strpos($method, 'test') === 0) {
                try {
                    $this->{$method}();
                } catch (Exception $ex) {
                    
                }
            }
        }
    }

    protected function assertEquals($expected, $actual)
    {
        if ($expected !== $actual) {
            throw new Exception('Failed asserting that ' . print_r($expected, true) . ' equals ' . print_r($actual, true));
        }
    }
}