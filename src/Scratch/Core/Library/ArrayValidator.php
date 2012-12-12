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

    public function setProperties(array $properties)
    {
        $this->properties = [];

        foreach ($properties as $name => $value) {
            $this->properties[$name] = new ArrayProperty($name, $value);
        }
    }

    public function setDefaults(array $defaults)
    {
        foreach ($defaults as $name => $value) {
            if (!isset($this->properties[$name])) {
                $this->properties[$name] = new ArrayProperty($name, $value, true);
            }
        }
    }

    public function getProperty($name)
    {
        if (isset($this->properties[$name])) {
            return $this->properties[$name]->getValue();
        }

        throw new Exception("Property '{$name}' is not set and has no default.", self::UNKNOWN_PROPERTY);
    }

    public function expect($property)
    {
        if (isset($this->properties[$property])) {
            return $this->properties[$property];
        }

        throw new Exception("Property '{$property}' is not set and has no default.", self::UNKNOWN_PROPERTY);
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
            $ex = new ValidationException();
            $ex->setViolations($violations);
            throw $ex;
        }
    }
}