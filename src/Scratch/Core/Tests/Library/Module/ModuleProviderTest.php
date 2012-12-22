<?php

namespace Scratch\Core\Library\Module;

require_once __DIR__ . '/modules/InvalidModule1.php';
require_once __DIR__ . '/modules/InvalidModule2.php';
require_once __DIR__ . '/modules/ValidModule1.php';
require_once __DIR__ . '/modules/ValidModule1.php';
require_once __DIR__ . '/modules/ValidModule2.php';
require_once __DIR__ . '/modules/ValidModule3.php';

class ModuleProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testModuleMustBeDefinedInAnActivePackage()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\UnknownModuleException');
        $provider = new ModuleProvider(['modules' => []], [], 'test');
        $provider->getModule('Unknown\Module');
    }

    public function testModuleClassMustBeLoadable()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\UnloadableModuleException');
        $provider = new ModuleProvider(['modules' => ['Unloadable\Module']], [], 'test');
        $provider->getModule('Unloadable\Module');
    }

    public function testModuleMustImplementModuleInterface()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\MissingModuleInterfaceException');
        $provider = new ModuleProvider(['modules' => ['InvalidModule1']], [], 'test');
        $provider->getModule('InvalidModule1');
    }

    public function testASingleValidModuleCanBeProvided()
    {
        $provider = new ModuleProvider(['modules' => ['ValidModule1']], [], 'test');
        $module = $provider->getModule('ValidModule1');
        $this->assertInstanceOf('ValidModule1', $module);
        $this->assertInstanceOf(ModuleProvider::MODULE_INTERFACE, $module);
    }

    public function testProviderAlwaysReturnsTheSameInstanceOfAModule()
    {
        $provider = new ModuleProvider(['modules' => ['ValidModule1']], [], 'test');
        $module = $provider->getModule('ValidModule1');
        $this->assertEquals($module, $provider->getModule('ValidModule1'));
        $this->assertEquals($module, $provider->getModule('ValidModule1'));
    }

    public function testDeclaredDependenciesMustBeReturnedInAnArray()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\InvalidDependenciesDeclarationException');
        $provider = new ModuleProvider(['modules' => ['InvalidModule2']], [], 'test');
        $provider->getModule('InvalidModule2');
    }

    public function testAModuleCanDeclareEmptyDependencies()
    {
        $provider = new ModuleProvider(['modules' => ['ValidModule2']], [], 'test');
        $module = $provider->getModule('ValidModule2');
        $this->assertInstanceOf('ValidModule2', $module);
        $this->assertInstanceOf(ModuleProvider::MODULE_INTERFACE, $module);
    }

    public function testAModuleCanDependOnAnotherModule()
    {
        $provider = new ModuleProvider(['modules' => ['ValidModule1', 'ValidModule3']], [], 'test');
        $module = $provider->getModule('ValidModule3');
        $this->assertInstanceOf('ValidModule3', $module);
        $this->assertInstanceOf('ValidModule1', $module->getModule1());
    }
}