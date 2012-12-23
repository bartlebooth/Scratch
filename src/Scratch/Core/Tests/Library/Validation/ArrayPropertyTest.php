<?php

namespace Scratch\Core\Library;

class ArrayPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstraintsCanBeIgnoredForPropertiesWithDefaultValue()
    {
        $property = new ArrayProperty('foo', 'bar', true);
        $property->toBeEmail()->toBeConfirmed();
        $this->assertEquals([], $property->getViolations());
    }

    public function testPropertyCanBeInitializedWithNotBlankViolation()
    {
        $property = new ArrayProperty('foo', 'bar', true, true);
        $this->assertEquals(['This field is mandatory'], $property->getViolations());
    }

    public function testAnExceptionIsThrownIfConstraintsAreNotIgnoredOnNotBlankViolation()
    {
        $this->setExpectedException('LogicException');
        new ArrayProperty('foo', 'bar', false, true);
    }

    /**
     * @dataProvider nonScalarValueProvider
     */
    public function testGetValueCanTryToForceFirstScalarValueForCompositeValues($value, $scalarValue)
    {
        $property = new ArrayProperty('foo', $value);
        $this->assertEquals($scalarValue, $property->getValue(true));
    }

    /**
     * @dataProvider nonForcableScalarValueProvider
     */
    public function testGetValueThrowsAnExceptionIfNonScalarValueCannotBeForcedToScalar($value)
    {
        try {
            $property = new ArrayProperty('foo', $value);
            $property->getValue(true);
            $this->fail('No exception thrown');
        } catch (\Exception $ex) {
            $this->assertEquals(ArrayProperty::NO_SCALAR_VALUE, $ex->getCode());
        }
    }

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
            $this->assertEquals(ArrayProperty::INVALID_VALUE_COUNT, $ex->getCode());
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

    /**
     * @dataProvider emailConstraintProvider
     */
    public function testEmailConstraint($email, array $errors)
    {
        $property = new ArrayProperty('foo', $email);
        $property->toBeEmail();
        $this->assertEquals($errors, $property->getViolations());
    }

    public function testFileConstraintThrowsAnExceptionIfPropertyIsNotAnArray()
    {
        try {
            $property = new ArrayProperty('foo', 'bar');
            $property->toBeFile();
            $this->fail('No exception thrown');
        } catch (\Exception $ex) {
            $this->assertEquals(ArrayProperty::INVALID_VALUE_TYPE, $ex->getCode());
        }
    }

    /**
     * @dataProvider invalidFileDataProvider
     */
    public function testFileConstraintThrowsAnExceptionIfFileArrayKeysAreMissing(array $file)
    {
        try {
            $property = new ArrayProperty('file', $file);
            $property->toBeFile();
            $this->fail('No exception thrown');
        } catch (\Exception $ex) {
            $this->assertEquals(ArrayProperty::INVALID_FILE_DATA, $ex->getCode());
        }
    }

    /**
     * @dataProvider fileConstraintProvider
     */
    public function testFileConstraint(array $file, $maxSize, $allowedMimeTypes, array $violations)
    {
        $property = new ArrayProperty('file', $file);
        $property->toBeFile($maxSize, $allowedMimeTypes);
        $this->assertEquals($violations, $property->getViolations());
    }

    public function nonScalarValueProvider()
    {
        $object = new \stdClass();
        $object->bar = 'bar';
        $object->baz = 'baz';

        return [
            [['bar'], 'bar'],
            [[['bar'], 'baz'], 'baz'],
            [$object, 'bar']
        ];
    }

    public function nonForcableScalarValueProvider()
    {
        return [
            [new \stdClass()],
            [[]],
        ];
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

    public function emailConstraintProvider()
    {
        return [
            ['foo@bar.baz', []],
            ['foo', ['Email address is not valid']],
            ['foo@', ['Email address is not valid']],
            ['foo@bar', ['Email address is not valid']],
            ['foo@bar.', ['Email address is not valid']],
        ];
    }

    public function invalidFileDataProvider()
    {
        return [
            [[]],
            [['name' => 'foo']],
            [['name' => 'foo', 'type' => 'text/html']],
            [['name' => 'foo', 'type' => 'text/html', 'size' => 123]],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/foo/bar/foo.html']],
        ];
    }

    public function fileConstraintProvider()
    {
        return [
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 12345], 1234, [], ['File is too large']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123], 1234, ['text/foo', 'text/bar'], ['Not allowed mime type']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123, 'error' => UPLOAD_ERR_INI_SIZE], 1234, [], ['File is too large']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123, 'error' => UPLOAD_ERR_FORM_SIZE], 1234, [], ['File is too large']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123, 'error' => UPLOAD_ERR_NO_FILE], 1234, [], ['This field is mandatory']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123, 'error' => UPLOAD_ERR_PARTIAL], 1234, [], ['Upload error']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123, 'error' => UPLOAD_ERR_NO_TMP_DIR], 1234, [], ['Server error (no tmp dir)']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123, 'error' => UPLOAD_ERR_CANT_WRITE], 1234, [], ['Server error (cannot write)']],
            [['name' => 'foo', 'type' => 'text/html', 'tmp_name' => '/bar/foo', 'size' => 123, 'error' => UPLOAD_ERR_EXTENSION], 1234, [], ['Server error (extension error)']],
        ];
    }
}