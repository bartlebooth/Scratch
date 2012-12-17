<?php

namespace Scratch\Core\Library;

use \Exception;

class ArrayValidator
{
    const UNKNOWN_PROPERTY = 10;

    private $properties;

    public function __construct()
    {
        $this->properties = [];
    }

    public function setProperties(array $properties, array $defaults = [])
    {
        $this->properties = [];

        foreach ($properties as $name => $value) {
            if ($this->isEmpty($value)) {
                if (array_key_exists($name, $defaults)) {
                    // use default when value is empty (other constraints will be ignored)
                    $this->properties[$name] = new ArrayProperty($name, $defaults[$name], true);
                    unset($defaults[$name]);
                } else {
                     // not blank violation when value is empty and has no default (other constraints will be ignored)
                    $this->properties[$name] = new ArrayProperty($name, $value, true, true);
                }
            } else {
                $this->properties[$name] = new ArrayProperty($name, $value);
            }
        }

        foreach ($defaults as $name => $value) {
            if (!isset($this->properties[$name])) {
                // use default when property is not set (other constraints will be ignored)
                $this->properties[$name] = new ArrayProperty($name, $defaults[$name], true);
            }
        }
    }

    // force scalar -> first scalar value or exception
    public function getProperty($name, $forceScalar = false)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name]->getValue($forceScalar);
        }

        throw new Exception("Property '{$name}' is not set, has no default and was not expected.", self::UNKNOWN_PROPERTY);
    }

    /**
     * Returns the property to be validated. If the property is not set and has no default value,
     * the returned property will have a null value, a not blank constraint violation and all
     * constraints applied on it will be ignored.
     *
     * @param string $property
     *
     * @return ArrayProperty
     */
    public function expect($property)
    {
        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }

        return $this->properties[$property] = new ArrayProperty($property, null, true, true);
    }

    public function getViolations()
    {
        $violations = [];

        foreach ($this->properties as $property) {
            if (count($propertyViolations = $property->getViolations()) > 0) {
                $violations[$property->getName() . '::errors'] = $propertyViolations;
            }
        }

        return $violations;
    }

    public function throwViolations()
    {
        if (count($violations = $this->getViolations()) > 0) {
            $ex = new ValidationException(print_r($violations, true));
            $ex->setViolations($violations);
            throw $ex;
        }
    }

    private function isEmpty($value) {
        if ($value === '' || $value === null) {
            return true;
        }

        if (is_array($value)) {
            $isEmpty = true;

            foreach ($value as $element) {
                if ($element !== '' && $element !== null) {
                    $isEmpty = false;
                    break;
                }
            }

            return $isEmpty;
        }

        return false;
    }
}