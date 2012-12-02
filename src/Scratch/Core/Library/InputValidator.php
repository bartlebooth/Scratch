<?php

namespace Scratch\Core\Library;

use \RuntimeException;

/**
 * Constraint examples :
 *      $validator->expect('foo')->toBeFile(1024);
 *      $validator->expect('bar')->toBeIn([1, 2, 3, 4], 1, 2); // choices, min, max
 *      $validator->expect('baz')->toPass(function ($property) {}, 'Not passed...');
 */
class InputValidator
{
    private $input;
    private $hasInput;
    private $currentProperty;
    private $currentPropertyValue;
    private $hasCurrentProperty;
    private $violations;

    public function __construct(array $input = [], $trimValues = true)
    {
        $this->hasInput = false;
        $this->hasCurrentProperty = false;
        $this->violations = [];
        $this->setInput($input, $trimValues);
    }

    public function setInput(array $input, $trimValues = true)
    {
        $this->hasInput = true;
        $this->input = $trimValues ? $this->trimValues($input) : $input;
    }

    public function getInput()
    {
        return $this->input;
    }

    public function getViolations()
    {
        return $this->violations;
    }

    public function expect($property, $index = 0)
    {
        if (!isset($this->input[$property])) {
            // Code 400 ? 422 ?
            throw new RuntimeException("Property '{$property}' was not given in the input");
        }

        if (is_array($this->input[$property])) {
            if (!isset($this->input[$property][$index])) {
                throw new RuntimeException("Cannot access property '{$property}' value at index '{$index}'");
            }

            $propertyValue = $this->input[$property][$index];
        } else {
            $propertyValue = $this->input[$property];
        }

        $this->currentProperty = $property;
        $this->currentPropertyValue = $propertyValue;
        $this->hasCurrentProperty = true;

        return $this;
    }

    public function toBeString($minLength = null, $maxLength = null)
    {
        if (!$this->hasCurrentProperty) {
            throw new RuntimeException('Constraint unapplicable : no current property.');
        }

        if (!is_string($this->currentPropertyValue)) {
            return $this->addViolation($this->currentProperty, 'Must be a string');
        }

        if (null !== $minLength && strlen($this->currentPropertyValue) < $minLength) {
            return $this->addViolation($this->currentProperty, "Must have a minimal length of {$minLength}");
        }

        if (null !== $maxLength && strlen($this->currentPropertyValue) > $maxLength) {
            return $this->addViolation($this->currentProperty, "Must have a maximal length of {$maxLength}");
        }

        return true;
    }

    public function toBeAlphanumeric($minLength = null, $maxLength = null)
    {
        if (true !== $result = $this->toBeString($minLength, $maxLength)) {
            return $result;
        }

        if (0 === preg_match('#^[a-zA-Z0-9]+$#', $this->currentPropertyValue)) {
            return $this->addViolation($this->currentProperty, 'Must be alphanumeric.');
        }

        return true;
    }

    public function toMatch($pattern, $errorMessage)
    {
        if (!$this->hasCurrentProperty) {
            throw new RuntimeException('Constraint unapplicable : no current property.');
        }

        if (0 === preg_match($pattern, $this->currentPropertyValue)) {
            return $this->addViolation($this->currentProperty, $errorMessage);
        }

        return true;
    }

    public function toBeConfirmed()
    {
        if (!$this->hasCurrentProperty) {
            throw new RuntimeException('Constraint unapplicable : no current property.');
        }

        if (!is_array($this->input[$this->currentProperty])) {
            throw new RuntimeException('Constraint unapplicable : property must an array of values.');
        }

        if (count($this->input[$this->currentProperty]) < 2) {
            throw new RuntimeException('Constraint unapplicable : property must have two values at least.');
        }

        $firstValue = array_shift($this->input[$this->currentProperty]);

        foreach ($this->input[$this->currentProperty] as $value) {
            if ($value !== $firstValue) {
                return $this->addViolation($this->currentProperty, "Values do not match.");
            }
        }

        $this->input[$this->currentProperty] = $firstValue;

        return true;
    }

    public function toBeUnique(\Closure $checker)
    {
        if (!$this->hasCurrentProperty) {
            throw new RuntimeException('Constraint unapplicable : no current property.');
        }

        if (true !== $checker($this->currentPropertyValue)) {
            return $this->addViolation($this->currentProperty, 'Already used.');
        }

        return true;
    }

    public function check()
    {
        if (count($this->violations) > 0) {
            $ex = new ValidationException();
            $ex->setViolations($this->violations);
            throw $ex;
        }

        return true;
    }

    private function addViolation($property, $message)
    {
        return $this->violations["{$property}::error"] = $message;
    }

    private function trimValues(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (is_string($value)) {
                $properties[$property] = trim($value);
            } elseif (is_array($value)) {
                $properties[$property] = $this->trimValues($value);
            }
        }

        return $properties;
    }
}