<?php

namespace Scratch\Core\Library\Module;

require_once __DIR__ . '/modules/ValidModule1.php';

use \ValidModule1;

class AbstractModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testSetApplicationParametersCanOnlyBeCalledOnce()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\ParametersAlreadySetException');
        $module = new ValidModule1();
        $module->setApplicationParameters(['modules' => ['ValidModule1']], ['locale' => 'en'], 'test');
        $module->setApplicationParameters(['foo'], ['bar'], 'baz');
    }
}