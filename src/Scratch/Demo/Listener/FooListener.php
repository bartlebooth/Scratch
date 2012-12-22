<?php

namespace Scratch\Demo\Listener;

use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\Core;

class FooListener implements ModuleConsumerInterface
{
    private $coreModule;

    public function __construct(Core $module)
    {
        $this->coreModule = $module;
    }

    public function onFoo($event)
    {
        echo 'Foo event dispatched to Demo\Listener\Foo::onFoo with "' . $event . '"</br>';
        echo 'Listener accessing param "env" via core module : ' . $this->coreModule->getEnvironment() . '</br>';

      //var_dump($this->container);
    }
}