<?php

namespace Scratch\Demo\Listener;

use Scratch\Core\Library\ContainerAwareInterface;
use Scratch\Core\Library\Container;

class FooListener implements ContainerAwareInterface
{
    private $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function onFoo($event)
    {
        echo 'Foo event dispatched to Demo\Listener\Foo::onFoo with "' . $event . '"</br>';
        echo 'Listener accessing container param "env" : ' . $this->container['env'] . '</br>';

      //var_dump($this->container);
    }
}