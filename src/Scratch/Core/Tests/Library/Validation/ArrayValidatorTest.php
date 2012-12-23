<?php

namespace Scratch\Core\Library;

use \Exception;

class ArrayValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ArrayValidator */
    private $validator;

    protected function setUp()
    {
        $this->validator = new ArrayValidator();
    }

    public function testThereAreNoViolationsIfNoPropertiesAreSet()
    {
        $this->validator->throwViolations();
        $this->assertEquals(0, count($this->validator->getViolations()));
    }

    public function testDefaultValuesAreAllowed()
    {
        $this->validator->setProperties([], ['foo' => 'bar']);
        $this->assertEquals('bar', $this->validator->getProperty('foo'));
    }

    public function testAnExceptionIsThrownIfAnUnknownPropertyIsAccessed()
    {
        try {
            $this->validator->getProperty('unknown');
            $this->fail('No exception thrown');
        } catch (Exception $ex) {
            $this->assertEquals(ArrayValidator::UNKNOWN_PROPERTY, $ex->getCode());
        }
    }

    public function testAPropertyWithNullValueAndNotBlankViolationIsReturnedIfANonGivenPropertyWithNoDefaultIsExpected()
    {
        $property = $this->validator->expect('unknown');
        $this->assertEquals(null, $property->getValue());
        $this->assertEquals(1, count($property->getViolations()));
        $this->assertEquals('This field is mandatory', $property->getViolations()[0]);
    }

    /**
     * @dataProvider emptyValueProvider
     */
    public function testAPropertyWithNotBlankViolationIsReturnedIfAPropertyWithEmptyValueAndNoDefaultIsExpected($value)
    {
        $this->validator->setProperties(['empty' => $value]);
        $property = $this->validator->expect('empty');
        $this->assertEquals(1, count($property->getViolations()));
        $this->assertEquals('This field is mandatory', $property->getViolations()[0]);
    }

    /**
     * @dataProvider emptyValueWithDefaultProvider
     */
    public function testAPropertyFilledWithDefaultAndIgnoringConstraintsIsReturnedIfAPropertyWithEmptyValueAndDefaultIsExpected($value, $default)
    {
        $this->validator->setProperties(['empty' => $value], ['empty' => $default]);
        $property = $this->validator->expect('empty')->toBeEmail()->toBeConfirmed();
        $this->assertEquals($default, $property->getValue());
        $this->assertEquals(0, count($property->getViolations()));
    }

    public function testViolationsAreIgnoredOnNonGivenPropertyWithDefaultValue()
    {
        $this->validator->setProperties([], ['foo' => null]);
        $this->validator->expect('foo')->toBeAlphanumeric(10, 20);
        $this->assertEquals(0, count($this->validator->getViolations()));
    }

    public function testConstraintIsAppliedOnGivenPropertyEvenIfADefaultValueIsProvided()
    {
        $this->validator->setProperties(['foo' => 'abcdef'], ['foo' => 'bar']);
        $this->validator->expect('foo')->toBeAlphanumeric(1, 4);
        $this->assertEquals(1, count($this->validator->getViolations()));
    }

    public function testViolationsAreCollectedInAnArrayForEachProperty()
    {
        $this->validator->setProperties(['foo' => 'bar']);
        $this->validator->expect('foo')
            ->toBeString(5, 10)
            ->toMatch('#\d+#', 'Error...');
        $this->assertEquals(2, count($this->validator->getViolations()['foo::errors']));
    }

    public function testViolationsCanBeThrownWithinAnException()
    {
        $this->setExpectedException('Scratch\Core\Library\ValidationException');
        $this->validator->setProperties(['foo' => '*$~~']);
        $this->validator->expect('foo')->toBeAlphanumeric();
        $this->validator->throwViolations();
    }

    public function emptyValueProvider()
    {
        return [
            [''],
            [null],
            [[]],
            [[null]],
            [['', '']],
            [[null, null]],
        ];
    }

    public function emptyValueWithDefaultProvider()
    {
        return [
            ['', 'foo'],
            [null, 'bar'],
            [[], ['foo', 'bar']],
            [[null], ['foo']],
            [['', ''], [null]],
            [[null, null], ['foo']],
        ];
    }
}