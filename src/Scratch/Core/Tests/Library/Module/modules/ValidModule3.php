<?php

use Scratch\Core\Library\Module\ModuleInterface;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

/**
 * Module depending on another module.
 */
class ValidModule3 implements ModuleInterface, ModuleConsumerInterface
{
    private $module1;

    public static function getConsumedModules()
    {
        return ['ValidModule1'];
    }

    public function __construct(ValidModule1 $module)
    {
        $this->module1 = $module;
    }

    public function setApplicationParameters(array $definitions, array $config, $environment)
    {
        // ...
    }

    public function getModule1()
    {
        return $this->module1;
    }
}