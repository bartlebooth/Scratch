<?php

namespace Scratch\Core\Library\Module;

interface ModuleConsumerInterface
{
    /**
     * Returns the fully qualified class name of every module whom the class
     * depends on.
     *
     * @return array[ModuleInterface]
     */
    static function getConsumedModules();
}