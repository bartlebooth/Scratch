<?php

namespace FakeVendor2\Package1\Controller;

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
        echo 'boum';
    }
}