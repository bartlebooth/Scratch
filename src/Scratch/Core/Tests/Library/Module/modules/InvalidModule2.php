<?php

use Scratch\Core\Library\Module\AbstractModule;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

/**
 * Invalid because dependencies must be type-hinted in the constructor.
 */
class InvalidModule2 extends AbstractModule implements ModuleConsumerInterface
{
    public function __construct($module1)
    {
        // ...
    }
}