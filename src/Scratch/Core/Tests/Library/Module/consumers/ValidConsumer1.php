<?php

use Scratch\Core\Library\Module\ModuleConsumerInterface;

class ValidConsumer1 implements ModuleConsumerInterface
{
    private $module1;
    private $module2;

    public function __construct(ValidModule1 $module1, ValidModule2 $module2)
    {
        $this->module1 = $module1;
        $this->module2 = $module2;
    }

    public function getModule1()
    {
        return $this->module1;
    }

    public function getModule2()
    {
        return $this->module2;
    }
}