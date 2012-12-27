<?php

namespace Scratch\Core\Module;

use Scratch\Core\Library\Module\ModuleManager;
use Scratch\Core\Module\Exception\NotFoundException;

require_once __DIR__ . '/controllers/Controller1.php';
require_once __DIR__ . '/controllers/Controller2.php';
require_once __DIR__ . '/controllers/Controller3.php';
require_once __DIR__ . '/listeners/Listener1.php';
require_once __DIR__ . '/listeners/Listener2.php';
require_once __DIR__ . '/../../Library/Module/modules/ValidModule1.php';
require_once __DIR__ . '/../../Library/Module/modules/ValidModule2.php';
require_once __DIR__ . '/models/Vendor1/Package1/Model/Driver/Driver1/Model1.php';
require_once __DIR__ . '/models/Vendor1/Package1/Model/Driver/Driver1/Model2.php';

class CoreModuleTest extends \PHPUnit_Framework_TestCase
{
    public function testSetModuleManagerCanOnlyBeCalledOnce()
    {
        $this->setExpectedException('Scratch\Core\Library\Module\Exception\ParametersAlreadySetException');
        $core = $this->buildCoreModule([], [], 'test');
        $core->setModuleManager(new ModuleManager(['foo'], ['bar'], ['baz']));
    }

    public function testMatchUrlChecksIfRouteIsDefined()
    {
        $core = $this->buildCoreModule(['routing' => []], [], 'test');
        $this->assertFalse($core->matchUrl('/unknown', 'GET', false));
    }

    public function testExecuteMatchUrlThrowsANotFoundExceptionIfRouteIsNotDefined()
    {
        try {
            $core = $this->buildCoreModule(['routing' => []], [], 'test');
            $core->matchUrl('/unknown', 'GET');
            $this->fail('No exception thrown');
        } catch (NotFoundException $ex) {
            $this->assertEquals(404, $ex->getCode());
        }
    }

    public function testMatchUrlLooksForAMatchingPrefixMethodCombination()
    {
        $core = $this->buildCoreModule(['routing' => ['routing1' => __DIR__.'/routing/routing1.php']], [], 'test');
        $this->assertTrue($core->matchUrl('/routing1/bar', 'GET', false));
        $this->assertFalse($core->matchUrl('/routing1/bar', 'POST', false));
        $this->assertFalse($core->matchUrl('/routing1/unknown', 'GET', false));
        $this->assertFalse($core->matchUrl('/routing1/unknown', 'FAKE', false));
    }

    public function testMatchUrlAllowsEmptyPrefixIfPatternIsEmpty()
    {
        $core = $this->buildCoreModule(['routing' => ['' => __DIR__.'/routing/routing1.php']], [], 'test');
        $this->assertTrue($core->matchUrl('/', 'GET', false));
        $this->assertFalse($core->matchUrl('/bar', 'GET', false));
    }

    public function testMatchUrlAllowsEmptyPatterns()
    {
        $core = $this->buildCoreModule(['routing' => ['baz' => __DIR__.'/routing/routing1.php']], [], 'test');
        $this->assertTrue($core->matchUrl('/baz', 'GET', false));
        $this->assertTrue($core->matchUrl('/baz/bar', 'GET', false));
    }

    public function testMatchUrlRemovesPathInfoTrailingSlashesIfAny()
    {
        $core = $this->buildCoreModule(['routing' => ['baz' => __DIR__.'/routing/routing1.php']], [], 'test');
        $this->assertTrue($core->matchUrl('/baz/', 'GET', false));
        $this->assertTrue($core->matchUrl('/baz/bar//', 'GET', false));
        $this->assertTrue($core->matchUrl('/baz/bar///', 'GET', false));
    }

    public function testExecuteMatchUrlExecutesTheMatchingControllerIfAny()
    {
        $core = $this->buildCoreModule(['routing' => ['baz' => __DIR__.'/routing/routing1.php']], [], 'test');
        ob_start();
        $core->matchUrl('/baz/', 'GET');
        $this->assertEquals('Foo', ob_get_clean());
    }

    public function testExecuteMatchUrlCallsTheControllerWithRequestParametersIfAny()
    {
        $core = $this->buildCoreModule(['routing' => ['baz' => __DIR__.'/routing/routing2.php']], [], 'test');
        ob_start();
        $core->matchUrl('/baz/foo/123/bat/abc', 'GET');
        $this->assertEquals('Foo with x = 123 and y = abc', ob_get_clean());
    }

    public function testControllerMustHandleItselfParametersDefaultValues()
    {
        $core = $this->buildCoreModule(['routing' => ['baz' => __DIR__.'/routing/routing2.php']], [], 'test');
        ob_start();
        $core->matchUrl('/baz/bar/ab//bat/456', 'GET');
        $this->assertEquals('Bar with x = ab, y = default-y and z = 456', ob_get_clean());
        ob_start();
        $core->matchUrl('/baz/bar/ab/123/bat/456', 'GET');
        $this->assertEquals('Bar with x = ab, y = 123 and z = 456', ob_get_clean());
    }

    public function testExecuteMatchUrlInjectsAnyNeededModuleIntoTheController()
    {
        $core = $this->buildCoreModule(
            [
                'routing' => ['bar' => __DIR__.'/routing/routing3.php'],
                'modules' => ['ValidModule1', 'ValidModule2']
            ],
            [],
            'test'
        );
        ob_start();
        $core->matchUrl('/bar/foo', 'POST');
        $this->assertEquals('Has modules of type ValidModule1 and ValidModule2', ob_get_clean());
    }

    public function testDispatchCallsEveryListenerAttachedToTheEvent()
    {
        $core = $this->buildCoreModule(
            [
                'listeners' => [
                    'foo' => ['Listener1::onFoo', 'Listener2::onFoo']
                ],
                'modules' => ['ValidModule1', 'ValidModule2']
            ],
            [],
            'test'
        );
        $event = new \stdClass();
        $core->dispatch('foo', $event);
        $this->assertEquals(2, count($event->listenerReferences));
        $this->assertInstanceOf('Listener1', $event->listenerReferences[0]);
        $this->assertInstanceOf('Listener2', $event->listenerReferences[1]);
    }

    public function testDispatchKeepsTheSameListenerInstanceForLaterCalls()
    {
        $core = $this->buildCoreModule(
            [
                'listeners' => [
                    'foo' => ['Listener1::onFoo'],
                    'bar' => ['Listener1::onBar']
                ]
            ],
            [],
            'test'
        );
        $firstEvent = new \stdClass();
        $core->dispatch('foo', $firstEvent);
        $this->assertInstanceOf('Listener1', $firstEvent->listenerReferences[0]);
        $secondEvent = new \stdClass();
        $core->dispatch('foo', $secondEvent);
        $this->assertEquals($firstEvent->listenerReferences, $secondEvent->listenerReferences);
    }

    public function testDispatchInjectAnyNeededModuleIntoTheListener()
    {
        $core = $this->buildCoreModule(
            [
                'listeners' => [
                    'foo' => ['Listener2::onFoo']
                ],
                'modules' => ['ValidModule1', 'ValidModule2']
            ],
            [],
            'test'
        );
        $event = new \stdClass();
        $core->dispatch('foo', $event);
        $this->assertEquals(1, count($event->listenerReferences));
        $this->assertInstanceOf('Listener2', $listener = $event->listenerReferences[0]);
        $this->assertInstanceOf('ValidModule1', $listener->getModule1());
        $this->assertInstanceOf('ValidModule2', $listener->getModule2());
    }

    /**
     * @dataProvider dbConfigProvider
     */
    public function testGetConnectionTriesToReturnsAPdoInstanceAccordingToConfigurationParameters(array $config, $env, $sqlState)
    {
        try {
            $core = $this->buildCoreModule([], $config, $env);
            $core->getConnection();
            $this->fail('No exception thrown');
        } catch (\PDOException $ex) {
            $this->assertEquals($sqlState, $ex->getCode());
        }
    }

    public function testGetConnectionMustKnowTheDriverSetInConfigurationParameters()
    {
        $this->setExpectedException('Scratch\Core\Module\Exception\UnknownDriverException');
        $core = $this->buildCoreModule([], ['db' => ['driver' => 'FooDriver']], 'prod');
        $core->getConnection();
    }

    public function testGetModelThrowsAnExceptionIfModelPackageIsUnknown()
    {
        $this->setExpectedException('Scratch\Core\Module\Exception\UnknownPackageException');
        $core = new CoreModule();
        $core->setApplicationParameters([], ['testDb' => ['driver' => 'Foo']], 'test');
        $core->getModel('Foo\Bar', 'BazModel');
    }

    public function testGetModelThrowsAnExceptionIfModelPackageIsNotActive()
    {
        $this->setExpectedException('Scratch\Core\Module\Exception\UnknownPackageException');
        $core = new CoreModule();
        $core->setApplicationParameters(
            [],
            [
                'testDb' => ['driver' => 'Foo'],
                'packages' => ['Foo\Bar' => false]
            ],
            'test'
        );
        $core->getModel('Foo\Bar', 'BazModel');
    }

    public function testGetModelThrowsAnExceptionIfModelClassIsNotLoadable()
    {
        $this->setExpectedException('Scratch\Core\Module\Exception\UnloadableModelException');
        $core = new CoreModule();
        $core->setApplicationParameters(
            [],
            [
                'testDb' => ['driver' => 'Baz'],
                'packages' => ['Foo\Bar' => true],
                'srcDir' => __DIR__
            ],
            'test'
        );
        $core->getModel('Foo\Bar', 'SomeModel');
    }

    public function testGetModelReturnsAnInstanceOfModelClass()
    {
        $core = $this->buildCoreModule(
            [],
            [
                'testDb' => ['driver' => 'Driver1'],
                'packages' => ['Vendor1\Package1' => true],
                'srcDir' => __DIR__ . '/models'
            ],
            'test'
        );
        $model = $core->getModel('Vendor1\Package1', 'Model1');
        $this->assertInstanceOf('Vendor1\Package1\Model\Driver\Driver1\Model1', $model);
    }

    public function testGetModelCallsModuleManagerToInjectModulesIntoModelIfNeeded()
    {
        $core = $this->buildCoreModule(
            [
                'modules' => ['ValidModule1']
            ],
            [
                'testDb' => ['driver' => 'Driver1'],
                'packages' => ['Vendor1\Package1' => true],
                'srcDir' => __DIR__ . '/models'
            ],
            'test'
        );
        $model = $core->getModel('Vendor1\Package1', 'Model2');
        $this->assertInstanceOf('Vendor1\Package1\Model\Driver\Driver1\Model2', $model);
        $this->assertInstanceOf('ValidModule1', $model->getModule1());
    }

    public function testGetSecurity()
    {
        $core = $this->getMock('Scratch\Core\Module\CoreModule', ['getModel']);
        $core->expects($this->once())
            ->method('getModel')
            ->with('Scratch/Core', 'UserModel')
            ->will($this->returnValue(
                $this->getMockBuilder('Scratch\Core\Model\Api\AbstractUserModel')
                    ->disableOriginalConstructor()
                    ->getMockForAbstractClass())
            );
        $this->assertInstanceOf('Scratch\Core\Library\Security', $core->getSecurity());
    }

    public function testGetValidator()
    {
        $core = new CoreModule();
        $this->assertInstanceOf('Scratch\Core\Library\Validation\ArrayValidator', $core->getValidator());
    }

    public function dbConfigProvider()
    {
        return [
            [
                [
                    'testDb' => [
                        'driver' => 'MySQL',
                        'host' => 'wrongHost',
                        'database' => 'wrongDb',
                        'user' => 'wrongUser',
                        'password' => 'wrongPassword'
                    ]
                ],
                'test',
                2005
            ]
        ];
    }

    private function buildCoreModule(array $definitions, array $config, $env)
    {
        $moduleManager = new ModuleManager($definitions, $config, $env);
        $core = new CoreModule();
        $core->setModuleManager($moduleManager);
        $core->setApplicationParameters($definitions, $config, $env);

        return $core;
    }
}