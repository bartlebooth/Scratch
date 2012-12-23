<?php

use Scratch\Core\Library\Module\ModuleConsumerInterface;

class Controller3 implements ModuleConsumerInterface
{
    private $module1;
    private $module2;

    public function __construct(ValidModule1 $module1, ValidModule2 $module2)
    {
        $this->module1 = $module1;
        $this->module2 = $module2;
    }

    public function foo()
    {
        echo 'Has modules of type ' . get_class($this->module1) . ' and ' . get_class($this->module2);
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