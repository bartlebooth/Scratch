<?php

namespace Scratch\Demo\Listener;

use Scratch\Core\Library\ContainerAware;

class FooListener extends ContainerAware
{
    public function onFoo($event)
    {
        echo 'Foo event dispatched to Demo\Listener\Foo::onFoo with "' . $event . '"</br>';
        echo 'Listener accessing container param "env" : ' . $this->container['env'] . '</br>';

      //var_dump($this->container);
    }
}