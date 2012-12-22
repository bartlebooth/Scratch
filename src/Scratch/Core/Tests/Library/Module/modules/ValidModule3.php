<?php

use Scratch\Core\Library\Module\AbstractModule;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

/**
 * Module depending on another module.
 */
class ValidModule3 extends AbstractModule implements ModuleConsumerInterface
{
    private $module1;

    public function __construct(ValidModule1 $module)
    {
        $this->module1 = $module;
    }

    public function getModule1()
    {
        return $this->module1;
    }
}