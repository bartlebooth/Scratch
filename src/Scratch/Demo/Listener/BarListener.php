<?php

namespace Scratch\Demo\Listener;

class BarListener
{
    public function onFoo($event)
    {
        echo 'Foo event dispatched to Demo\Listener\Bar::onFoo';
    }

    public function onBar($event)
    {
        echo 'Bar event dispatched to Demo\Listener\Bar::onBar';
    }
}