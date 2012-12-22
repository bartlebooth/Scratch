<?php

use Scratch\Core\Library\Module\ModuleInterface;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

/**
 * Module declaring empty dependencies (useless but tolerated)
 */
class ValidModule2 implements ModuleInterface, ModuleConsumerInterface
{
    public static function getConsumedModules()
    {
        return [];
    }

    public function setApplicationParameters(array $definitions, array $config, $environment)
    {
        // ...
    }
}