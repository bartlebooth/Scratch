<?php

namespace Scratch\Core\Library;

abstract class ContainerAware
{
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }
}