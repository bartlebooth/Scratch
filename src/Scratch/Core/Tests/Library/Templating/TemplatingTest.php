<?php

namespace Scratch\Core\Library\Templating;

class TemplatingTest extends \PHPUnit_Framework_TestCase
{
    public function testVarHelperReturnsTheVariableValueIfVariableIsDefined()
    {
        $var = $this->getHelper('var', ['foo' => ['bar', 'baz']]);
        $this->assertEquals(['bar', 'baz'], $var('foo'));
    }

    public function testVarHelperEscapesTheVariableValueIfValueIsAString()
    {
        $var = $this->getHelper('var', ['foo' => '<script>alert("ok");</script>']);
        $this->assertEquals(htmlspecialchars('<script>alert("ok");</script>', ENT_QUOTES, 'UTF-8'), $var('foo'));
    }

    public function testVarHelperReturnsTheDefaultValueIfProvidedAndIfVariableIsNotDefined()
    {
        $var = $this->getHelper('var');
        $this->assertEquals('bar', $var('foo', 'bar'));
    }

    public function testVarHelperReturnsNullInProdEnvironmentIfVariableIsNotDefinedAndHasNoDefault()
    {
        $var = $this->getHelper('var', [], 'prod');
        $this->assertNull($var('unknownVariable'));
    }

    /**
     * @dataProvider nonProdEnvironmentProvider
     */
    public function testVarHelperThrowsAnExceptionInNonProdEnvironmentIfVariableIsNotDefinedAndHasNoDefault($environment)
    {
        $this->setExpectedException('Scratch\Core\Library\Templating\Exception\UndefinedVariableException');
        $var = $this->getHelper('var', [], $environment);
        $var('unknownVariable');
    }

    public function testRawHelperBehavesLikeVarHelperButDoesntEscapeStrings()
    {
        $raw = $this->getHelper('raw', ['foo' => ['bar', 'baz'], 'bat' => '<script>alert("ok");</script>']);
        $this->assertEquals(['bar', 'baz'], $raw('foo'));
        $this->assertEquals('bar', $raw('boo', 'bar'));
        $this->assertEquals('<script>alert("ok");</script>', $raw('bat'));
    }

    public function testPathHelperConcatenatesTheWebPathOfTheApplicationAndTheGivenPathInfo()
    {
        $path = $this->getHelper('path', [], 'prod');
        $this->assertEquals('localhost/foo/bar.php/unknown/path', $path('/unknown/path'));
    }

    /**
     * @dataProvider nonProdEnvironmentProvider
     */
    public function testPathHelperThrowsAnExceptionInNonProdEnvironmentIfThePathDoesntMatchAnyDefinedRoute($environment)
    {
        $this->setExpectedException('Scratch\Core\Module\Exception\NotFoundException');
        $path = $this->getHelper('path', [], $environment);
        $path('/unknown/path');
    }

    public function testAssetHelperConcatenatesPublicWebPathAndAssetFile()
    {
        $asset = $this->getHelper('asset');
        $this->assertEquals('localhost/foo/bar/baz.css', $asset('/bar/baz.css'));
    }

    /**
     * @dataProvider simpleFormRowProvider
     */
    public function testFormRowHelperForSimpleFields($variables, $type, $options, $expectedControls)
    {
        $formRow = $this->getHelper('formRow', $variables);
        $this->assertEquals(
            '<div class="control-group">'
            . '<label class="control-label" for="foo">Foo :</label>'
            . '<div class="controls">'
            . $expectedControls
            . '<span class="help-inline"><ul></ul></span></div></div>',
            $formRow($type, 'foo', 'Foo', $options)
        );
    }

    /**
     * @dataProvider compositeFormRowProvider
     */
    public function testFormRowHelperForCompositeFields($variables, $type, $options, $expectedControls)
    {
        $formRow = $this->getHelper('formRow', $variables);
        $this->assertEquals(
            '<div class="control-group">'
            . '<label class="control-label" for="foo">Foo :</label>'
            . '<div class="controls">'
            . $expectedControls
            . '<span class="help-inline"><ul></ul></span></div></div>',
            $formRow($type, 'foo', 'Foo', $options)
        );
    }

    /**
     * @dataProvider compositeRowTypeProvider
     */
    public function testCompositeFormRowsNeedAnItemVariableForOptions()
    {
        $this->setExpectedException('Scratch\Core\Library\Templating\Exception\UndefinedVariableException');
        $formRow = $this->getHelper('formRow');
        $formRow('select', 'foo', 'Foo');
    }

    /**
     * @dataProvider formRowErrorProvider
     */
    public function testFormRowErrorsAreDisplayedIfAnErrorVariableIsProvided(array $variables, $type, $expectedErrorOutput)
    {
        $formRow = $this->getHelper('formRow', $variables);
        $this->assertContains($expectedErrorOutput, $formRow($type, 'foo', 'Foo'));
    }

    public function testFormRowHelperThrowsAnExceptionIfControlTypeIsUnknown()
    {
        $this->setExpectedException('Scratch\Core\Library\Templating\Exception\UnknownControlTypeException');
        $formRow = $this->getHelper('formRow');
        $formRow('unknownType', 'foo', 'Foo');
    }

    public function testCallHelperGetsARendererInstanceAndCallsItsRenderMethod()
    {
        $call = $this->getHelper('call');
        $this->assertEquals('Foo renderer output with bar = baz', $call('Foo\Renderer', ['bar' => 'baz']));
    }

    public function testConfigHelper()
    {
        $config = $this->getHelper('config');
        $this->assertEquals('bar', $config('foo'));
        $this->setExpectedException('Scratch\Core\Library\Templating\Exception\UnknownConfigurationParameterException');
        $config('unknownParameter');
    }

    public function testFlashesHelper()
    {
        $this->markTestSkipped('Not implemented yet');
    }

    /**
     * @dataProvider templateProvider
     */
    public function testRenderTemplate($template, array $variables, $expectedOutput)
    {
        $templating = $this->getTemplating();
        $output = $templating->render(__DIR__ . '/templates/' . $template, $variables);
        $this->assertEquals($expectedOutput, $output);
    }

    public function testDisplayTemplate()
    {
        $templating = $this->getTemplating();
        ob_start();
        $templating->display(__DIR__ . '/templates/tpl1.html.php');
        $this->assertEquals('<h1>Template 1</h1>', ob_get_clean());
    }

    public function nonProdEnvironmentProvider()
    {
        return [
            ['dev'],
            ['test']
        ];
    }

    public function simpleFormRowProvider()
    {
        return [
            [[], 'text', [], '<input type="text" name="foo" value="" />'],
            [[], 'password', [], '<input type="password" name="foo" value="" />'],
            [[], 'textarea', [], '<textarea name="foo" ></textarea>'],
            [[], 'file', [], '<input type="file" name="foo" value="" />'],
            [[], 'file', ['size' => 1234], '<input type="hidden" name="MAX_FILE_SIZE" value="1234" /><input type="file" name="foo" value="" />'],
            [[], 'text', ['disabled' => true], '<input type="text" name="foo" value="" disabled="disabled"/>'],
            [[], 'text', ['arrayField' => true], '<input type="text" name="foo[]" value="" />'],
            [[], 'password', ['disabled' => true], '<input type="password" name="foo" value="" disabled="disabled"/>'],
            [[], 'password', ['arrayField' => true], '<input type="password" name="foo[]" value="" />'],
            [[], 'textarea', ['disabled' => true], '<textarea name="foo" disabled="disabled"></textarea>'],
            [[], 'textarea', ['size' => 1234], '<textarea name="foo" maxlength="1234" ></textarea>'],
            [[], 'file', ['disabled' => true], '<input type="file" name="foo" value="" disabled="disabled"/>'],
            [['foo' => 'abc'], 'text', [], '<input type="text" name="foo" value="abc" />'],
            [['foo' => 'xyz'], 'password', [], '<input type="password" name="foo" value="" />'],
            [['foo' => '123'], 'textarea', [], '<textarea name="foo" >123</textarea>'],
        ];
    }

    public function compositeFormRowProvider()
    {
        return [
            [['foo::items' => [1 => 'A', 2 => 'B']], 'select', [], '<select name="foo" ><option value="1" >A</option><option value="2" >B</option></select>'],
            [['foo::items' => [1 => 'A', 2 => 'B']], 'select', ['disabled' => true], '<select name="foo" disabled="disabled"><option value="1" >A</option><option value="2" >B</option></select>'],
            [['foo::items' => [1 => 'A', 2 => 'B']], 'selectMultiple', [], '<select name="foo[]"  multiple="multiple"><option value="1" >A</option><option value="2" >B</option></select>'],
            [['foo::items' => [1 => 'A', 2 => 'B']], 'selectMultiple', ['disabled' => true], '<select name="foo[]" disabled="disabled" multiple="multiple"><option value="1" >A</option><option value="2" >B</option></select>'],
            [['foo::items' => [1 => 'A', 2 => 'B']], 'radio', [], '<input type="radio" name="foo" value="1" />A<input type="radio" name="foo" value="2" />B'],
            [['foo::items' => [1 => 'A', 2 => 'B']], 'radio', ['disabled' => true], '<input type="radio" name="foo" value="1" disabled="disabled"/>A<input type="radio" name="foo" value="2" disabled="disabled"/>B'],
            [['foo::items' => [1 => 'A', 2 => 'B']], 'checkbox', [], '<input type="checkbox" name="foo[]" value="1" />A<input type="checkbox" name="foo[]" value="2" />B'],
            [['foo::items' => [1 => 'A', 2 => 'B']], 'checkbox', ['disabled' => true], '<input type="checkbox" name="foo[]" value="1" disabled="disabled"/>A<input type="checkbox" name="foo[]" value="2" disabled="disabled"/>B'],
            [['foo::items' => [1 => 'A', 2 => 'B'], 'foo' => 1], 'select', [], '<select name="foo" ><option value="1" selected="selected">A</option><option value="2" >B</option></select>'],
            [['foo::items' => [1 => 'A', 2 => 'B'], 'foo' => [1]], 'selectMultiple', [], '<select name="foo[]"  multiple="multiple"><option value="1" selected="selected">A</option><option value="2" >B</option></select>'],
            [['foo::items' => [1 => 'A', 2 => 'B'], 'foo' => [1, 2]], 'selectMultiple', [], '<select name="foo[]"  multiple="multiple"><option value="1" selected="selected">A</option><option value="2" selected="selected">B</option></select>'],
            [['foo::items' => [1 => 'A', 2 => 'B'], 'foo' => 1], 'radio', [], '<input type="radio" name="foo" value="1"  checked="checked"/>A<input type="radio" name="foo" value="2" />B'],
            [['foo::items' => [1 => 'A', 2 => 'B'], 'foo' => [1]], 'checkbox', [], '<input type="checkbox" name="foo[]" value="1"  checked="checked"/>A<input type="checkbox" name="foo[]" value="2" />B'],
            [['foo::items' => [1 => 'A', 2 => 'B'], 'foo' => [1, 2]], 'checkbox', [], '<input type="checkbox" name="foo[]" value="1"  checked="checked"/>A<input type="checkbox" name="foo[]" value="2"  checked="checked"/>B'],
        ];
    }

    public function compositeRowTypeProvider()
    {
        return [
            ['select'],
            ['selectMultiple'],
            ['radio'],
            ['checkbox'],
        ];
    }

    public function formRowErrorProvider()
    {
        return [
            [['foo::errors' => ['Foo error...']], 'text', '<ul><li>Foo error...</li></ul>'],
            [['foo::errors' => ['Error 1', 'Error 2'], 'foo::items' => [1 => 'A', 2 => 'B']], 'select', '<ul><li>Error 1</li><li>Error 2</li></ul>']
        ];
    }

    public function templateProvider()
    {
        $eol = PHP_EOL;

        return [
            ['tpl1.html.php', [], '<h1>Template 1</h1>'],
            ['tpl2.html.php', ['foo' => 'Bar'], "<h1>Template 2</h1>$eol<i>Bar</i>"],
            ['tpl3.html.php', [], "<h1>Template 3</h1>$eol<path>ok</path>$eol<asset>ok</asset>$eol<formRow>ok</formRow>$eol<call>ok</call>$eol<config>ok</config>$eol<raw>ok</raw>$eol<flashes>ok</flashes>"],
            ['tpl4.html.php', [], "<h1>Template 4</h1>$eol<h1>Template 1</h1>"],
        ];
    }

    private function getTemplating($environment = 'test')
    {
        $core = $this->getMock('Scratch\Core\Module\CoreModule');
        $core->expects($this->any())
            ->method('getContext')
            ->will($this->returnValue(['frontScript' => 'localhost/foo/bar.php']));
        $core->expects($this->any())
            ->method('getEnvironment')
            ->will($this->returnValue($environment));
        $core->expects($this->any())
            ->method('matchUrl')
            ->with('/unknown/path', 'GET', false)
            ->will($this->returnValue(false));
        $renderer = $this->getMock('Scratch\Core\Library\RendererInterface');
        $renderer->expects($this->any())
            ->method('render')
            ->with(['bar' => 'baz'])
            ->will($this->returnValue('Foo renderer output with bar = baz'));
        $core->expects($this->any())
            ->method('getRenderer')
            ->with('Foo\Renderer')
            ->will($this->returnValue($renderer));
        $core->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue(['foo' => 'bar']));

        return new Templating($core);
    }

    private function getHelper($helper, array $variables = [], $environment = 'test')
    {
        $templating = $this->getTemplating($environment);
        $templating->setVariables($variables);

        return $templating->getHelpers()[$helper];
    }
}