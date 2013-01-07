<?php

namespace FakeVendor1\Package2\Controller;

use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;

class Controller1 implements ModuleConsumerInterface
{
    private $core;

    public function __construct(CoreModule $core)
    {
        $this->core = $core;
    }

    public function action1()
    {
        header('Location: ' . $this->core->getContext()['frontScript'] . '/prefix1/action1');
        echo 'Redirecting to FakeVendor1\Package1\Controller1::action1...';
    }

    public function action2()
    {
        echo '<html><head></head><body><h1>Foo</h1><div id="bar"><span>baz</span><span>bat</span></div></body></html>';
    }
}