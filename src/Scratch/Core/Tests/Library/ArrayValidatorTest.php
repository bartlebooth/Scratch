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
        $this->validator->setProperties([]);
        $this->validator->setDefaults(['foo' => 'bar']);
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

    public function testAnExceptionIsThrownIfANonGivenPropertyWithNoDefaultIsExpected()
    {
        try {
            $this->validator->expect('unknown')->toBeString();
            $this->fail('No exception thrown');
        } catch (Exception $ex) {
            $this->assertEquals(ArrayValidator::UNKNOWN_PROPERTY, $ex->getCode());
        }
    }

    public function testConstraintsAreBypassedOnNonGivenPropertyWithDefault()
    {
        $this->validator->setDefaults(['foo' => null]);
        $this->validator->expect('foo')->toBeAlphanumeric(10, 20);
        $this->assertEquals(0, count($this->validator->getViolations()));
    }

    public function testConstraintIsAppliedOnGivenPropertyEvenIfADefaultIsProvided()
    {
        $this->validator->setDefaults(['foo' => 'bar']);
        $this->validator->setProperties(['foo' => 'abcdef']);
        $this->validator->expect('foo')->toBeAlphanumeric(1, 4);
        $this->assertEquals(1, count($this->validator->getViolations()));
    }

    public function testViolationsAreCollectedInAnArrayForEachProperty()
    {
        $this->validator->setProperties(['foo' => 'bar']);
        $this->validator->expect('foo')
            ->toBeString(5, 10)
            ->toMatch('#\d+#', 'Error...');
        $this->assertEquals(2, count($this->validator->getViolations()['foo']));
    }

    public function testViolationsCanBeThrownWithinAnException()
    {
        $this->setExpectedException('Scratch\Core\Library\ValidationException');
        $this->validator->setProperties(['foo' => '*$~~']);
        $this->validator->expect('foo')->toBeAlphanumeric();
        $this->validator->throwViolations();
    }
}