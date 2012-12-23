<?php

namespace Scratch\Core\Module;

use Scratch\Core\Library\Module\ModuleManager;
use Scratch\Core\Module\Exception\NotFoundException;

require_once __DIR__ . '/controllers/Controller1.php';
require_once __DIR__ . '/controllers/Controller2.php';

class CoreModuleTest extends \PHPUnit_Framework_TestCase
{
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

    private function buildCoreModule(array $definitions, array $config, $env)
    {
        $moduleManager = new ModuleManager($definitions, $config, $env);
        $core = new CoreModule();
        $core->setModuleManager($moduleManager);
        $core->setApplicationParameters($definitions, $config, $env);

        return $core;
    }
}