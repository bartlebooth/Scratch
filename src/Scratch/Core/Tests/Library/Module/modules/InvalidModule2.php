<?php

use Scratch\Core\Library\Module\ModuleInterface;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

/**
 * Invalid because dependencies must be returned in an array
 */
class InvalidModule2 implements ModuleInterface, ModuleConsumerInterface
{
    public static function getConsumedModules()
    {
        return 'Foo\Bar';
    }

    public function __construct(Foo\Bar $module)
    {
        // ...
    }

    public function setApplicationParameters(array $definitions, array $config, $environment)
    {
        // ...
    }
}