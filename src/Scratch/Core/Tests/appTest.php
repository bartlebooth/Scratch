<?php

class appTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultAppInvocation()
    {
        $this->markTestSkipped('Obsolete');

        $_SERVER['REMOTE_ADDR'] = '255.255.123.456';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        require __DIR__.'/../../../../web/app.php';
    }
}