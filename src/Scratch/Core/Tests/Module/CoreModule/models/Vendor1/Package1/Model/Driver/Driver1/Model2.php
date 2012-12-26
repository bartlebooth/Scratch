<?php

namespace Vendor1\Package1\Model\Driver\Driver1;

use \ValidModule1;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

class Model2 implements ModuleConsumerInterface
{
    private $module1;

    public function __construct(ValidModule1 $module1)
    {
        $this->module1 = $module1;
    }

    public function getModule1()
    {
        return $this->module1;
    }
}