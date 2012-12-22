<?php

use Scratch\Core\Library\Module\AbstractModule;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

/**
 * Module implementing ModuleConsumerInterface but not declaring
 * any dependency (useless but tolerated)
 */
class ValidModule2 extends AbstractModule implements ModuleConsumerInterface
{
}