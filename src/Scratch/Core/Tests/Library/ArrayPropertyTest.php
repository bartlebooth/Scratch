<?php

namespace Scratch\Core\Library;

class ArrayPropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider stringConstraintProvider
     */
    public function testStringConstraint($name, $value, $minLength, $maxLength, array $errors)
    {
        $property = new ArrayProperty($name, $value);
        $property->toBeString($minLength, $maxLength);
        $this->assertEquals($errors, $property->getViolations());
    }

    public function testStringConstraintThrowsAnExceptionIfMinLengthIsGreaterThanMaxLength()
    {
        $this->setExpectedException('InvalidArgumentException');
        $property = new ArrayProperty('foo', 'bar');
        $property->toBeString(4, 2);
    }

    /**
     * @dataProvider alphanumericConstraintProvider
     */
    public function testAlphanumericConstraint($name, $value, $minLength, $maxLength, array $errors)
    {
        $property = new ArrayProperty($name, $value);
        $property->toBeAlphanumeric($minLength, $maxLength);
        $this->assertEquals($errors, $property->getViolations());
    }

    /**
     * @dataProvider matchPatternConstraintProvider
     */
    public function testMatchPatternConstraint($name, $value, $pattern, $errorMsg, array $errors)
    {
        $property = new ArrayProperty($name, $value);
        $property->toMatch($pattern, $errorMsg);
        $this->assertEquals($errors, $property->getViolations());
        count($property->getViolations()) > 0 && $this->assertEquals($errorMsg, $property->getViolations()[0]);
    }

    public function testConfirmedConstraintThrowsAnExceptionIfPropertyIsNotAnArray()
    {
        try {
            $property = new ArrayProperty('foo', 'bar');
            $property->toBeConfirmed();
            $this->fail('No exception thrown');
        } catch (\Exception $ex) {
            $this->assertEquals(ArrayProperty::INVALID_VALUE_TYPE, $ex->getCode());
        }
    }

    public function testConfirmedConstraintThrowsAnExceptionIfArrayCountIsLesserThanTwo()
    {
        try {
            $property = new ArrayProperty('foo', ['bar']);
            $property->toBeConfirmed();
            $this->fail('No exception thrown');
        } catch (\Exception $ex) {
            $this->assertEquals(ArrayProperty::INVALID_VALUE, $ex->getCode());
        }
    }

    /**
     * @dataProvider confirmedConstraintProvider
     */
    public function testConfirmedConstraint($name, $value, array $errors)
    {
        $property = new ArrayProperty($name, $value);
        $property->toBeConfirmed();
        $this->assertEquals($errors, $property->getViolations());
    }

    /**
     * @dataProvider uniqueConstraintProvider
     */
    public function testUniqueConstraint($name, $value, \Closure $isUnique, array $errors)
    {
        $property = new ArrayProperty($name, $value);
        $property->toBeUnique($isUnique);
        $this->assertEquals($errors, $property->getViolations());
    }

    public function stringConstraintProvider()
    {
        return [
            ['foo', 'bar', null, null, []],
            ['foo', null, null, null, ['Must be a string']],
            ['foo', 'a', 3, 10, ['Must have a minimal length of 3']],
            ['foo', 'abc', 1, 2, ['Must have a maximal length of 2']]
        ];
    }

    public function alphanumericConstraintProvider()
    {
        return [
            ['foo', 'bar', null, null, []],
            ['foo', 'a', 3, 10, ['Must have a minimal length of 3']],
            ['foo', '$~é', 1, 10, ['Must be alphanumeric']],
            ['foo', 'a  f  ', 1, 10, ['Must be alphanumeric']]
        ];
    }

    public function matchPatternConstraintProvider()
    {
        return [
            ['foo', '123', '#\d+#', 'Error...', []],
            ['foo', 'abc', '#\d+#', 'Must be a digit', ['Must be a digit']],
            ['foo', 'def', '#[a-c]{3}#', 'Error...', ['Error...']]
        ];
    }

    public function confirmedConstraintProvider()
    {
        return [
            ['foo', ['bar', 'bar'], []],
            ['foo', ['bar', 'baz'], ['Values do not match']],
            ['foo', ['bar', 'baz', 'bak'], ['Values do not match']]
        ];
    }

    public function uniqueConstraintProvider()
    {
        return [
            ['foo', 'bar', function () { return true; }, []],
            ['foo', 'baz', function () { return false; }, ['Already used']]
        ];
    }
}