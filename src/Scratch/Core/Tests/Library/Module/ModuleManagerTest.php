<?php

namespace Scratch\Core\Library\Module;

require_once __DIR__ . '/modules/InvalidModule1.php';
require_once __DIR__ . '/modules/InvalidModule2.php';
require_once __DIR__ . '/modules/ValidModule1.php';
require_once __DIR__ . '/modules/ValidModule1.php';
require_once __DIR__ . '/modules/ValidModule2.php';
require_once __DIR__ . '/modules/ValidModule3.php';
require_once __DIR__ . '/consumers/ValidConsumer1.php';
require_once __DIR__ . '/consumers/FalseConsumer.php';

class ModuleManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testModuleMustBeDefinedInAnActivePackage()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\UnknownModuleException');
        $manager = new ModuleManager(['modules' => []], [], 'test');
        $manager->getModule('Unknown\Module');
    }

    public function testModuleClassMustBeLoadable()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\UnloadableModuleException');
        $manager = new ModuleManager(['modules' => ['Unloadable\Module']], [], 'test');
        $manager->getModule('Unloadable\Module');
    }

    public function testModuleMustImplementModuleInterface()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\InvalidModuleClassException');
        $manager = new ModuleManager(['modules' => ['InvalidModule1']], [], 'test');
        $manager->getModule('InvalidModule1');
    }

    public function testASingleValidModuleCanBeProvided()
    {
        $manager = new ModuleManager(['modules' => ['ValidModule1']], [], 'test');
        $module = $manager->getModule('ValidModule1');
        $this->assertInstanceOf('ValidModule1', $module);
        $this->assertInstanceOf(ModuleManager::MODULE_CLASS, $module);
    }

    public function testProviderAlwaysReturnsTheSameInstanceOfAModule()
    {
        $manager = new ModuleManager(['modules' => ['ValidModule1']], [], 'test');
        $module = $manager->getModule('ValidModule1');
        $this->assertEquals($module, $manager->getModule('ValidModule1'));
        $this->assertEquals($module, $manager->getModule('ValidModule1'));
    }

    public function testDeclaredDependenciesMustBeTypeHintedInTheConstructor()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\InvalidDependenciesDeclarationException');
        $manager = new ModuleManager(['modules' => ['InvalidModule2']], [], 'test');
        $manager->getModule('InvalidModule2');
    }

    public function testAModuleCanDeclareEmptyDependencies()
    {
        $manager = new ModuleManager(['modules' => ['ValidModule2']], [], 'test');
        $module = $manager->getModule('ValidModule2');
        $this->assertInstanceOf('ValidModule2', $module);
        $this->assertInstanceOf(ModuleManager::MODULE_CLASS, $module);
    }

    public function testAModuleCanDependOnAnotherModule()
    {
        $manager = new ModuleManager(['modules' => ['ValidModule1', 'ValidModule3']], [], 'test');
        $module = $manager->getModule('ValidModule3');
        $this->assertInstanceOf('ValidModule3', $module);
        $this->assertInstanceOf('ValidModule1', $module->getModule1());
    }

    public function testApplicationParametersArePassedToManagedModules()
    {
        $definitions = ['modules' => ['ValidModule1'], 'routing' => []];
        $configuration = ['locale' => 'en'];
        $environment = 'test';
        $manager = new ModuleManager($definitions, $configuration, $environment);
        $module = $manager->getModule('ValidModule1');
        $this->assertEquals($definitions, $module->getDefinitions());
        $this->assertEquals($configuration, $module->getConfiguration());
        $this->assertEquals($environment, $module->getEnvironment());
    }

    public function testCreateConsumerInjectModulesIntoANewFqcnInstance()
    {
        $manager = new ModuleManager(['modules' => ['ValidModule1', 'ValidModule2']], [], 'test');
        $consumer = $manager->createConsumer('ValidConsumer1');
        $this->assertInstanceOf('ValidConsumer1', $consumer);
        $this->assertInstanceOf('ValidModule1', $consumer->getModule1());
        $this->assertInstanceOf('ValidModule2', $consumer->getModule2());
    }

    public function testCreateConsumerJustReturnsANewFqcnInstanceIfClassDoesntImplementModuleConsumerInterface()
    {
        $manager = new ModuleManager(['modules' => []], [], 'test');
        $this->assertInstanceOf('FalseConsumer', $manager->createConsumer('FalseConsumer'));
    }
}