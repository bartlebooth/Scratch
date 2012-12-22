<?php

use Scratch\Core\Library\Module\ModuleInterface;

/**
 * Simplest valid module.
 */
class ValidModule1 implements ModuleInterface
{
    public function setApplicationParameters(array $definitions, array $config, $environment)
    {
        // ...
    }
}