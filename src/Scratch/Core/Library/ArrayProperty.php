<?php

namespace Scratch\Core\Library;

use \Exception;
use \InvalidArgumentException;
use \Closure;

class ArrayProperty
{
    const INVALID_VALUE_TYPE = 20;
    const INVALID_VALUE_COUNT = 21;
    const NO_SCALAR_VALUE = 22;

    private $name;
    private $value;
    private $hasScalarValue;
    private $scalarValue;
    private $ignoreViolations;
    private $violations;

    public function __construct($name, $value, $ignoreViolations = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->hasScalarValue = false;
        $this->ignoreViolations = $ignoreViolations;
        $this->violations = [];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        try {
            return $this->getScalarValue();
        } catch (Exception $ex) {
            if ($ex->getCode() === self::NO_SCALAR_VALUE) {
                return $this->value;
            }

            throw $ex;
        }
    }

    public function getViolations()
    {
        if ($this->ignoreViolations) {
            return [];
        }

        return $this->violations;
    }

    public function toBeString($minLength = null, $maxLength = null)
    {
        $this->checkStringLengthConstraint($minLength, $maxLength);

        return $this;
    }

    public function toBeAlphanumeric($minLength = null, $maxLength = null)
    {
        $this->checkStringLengthConstraint($minLength, $maxLength);

        if (0 === preg_match('#^[a-zA-Z0-9]+$#', $this->getScalarValue())) {
            $this->violations[] = 'Must be alphanumeric';
        }

        return $this;
    }

    public function toMatch($pattern, $errorMessage)
    {
        if (0 === preg_match($pattern, $this->getScalarValue())) {
            $this->violations[] = $errorMessage;
        }

        return $this;
    }

    public function toBeConfirmed()
    {
        if (!is_array($this->value)) {
            throw new Exception('Constraint unapplicable : property must be an array of values.', self::INVALID_VALUE_TYPE);
        }

        if (count($this->value) < 2) {
            throw new Exception('Constraint unapplicable : property must have two values at least.', self::INVALID_VALUE_COUNT);
        }

        $firstElement = reset($this->value);
        next($this->value);

        foreach ($this->value as $element) {
            if ($element !== $firstElement) {
                $this->violations[] = 'Values do not match';
                break;
            }
        }

        return $this;
    }

    public function toBeUnique(Closure $isUnique)
    {
        if (true !== $isUnique($this->getScalarValue())) {
            $this->violations[] = 'Already used';
        }

        return $this;
    }

    private function getScalarValue()
    {
        if ($this->hasScalarValue) {
            return $this->scalarValue;
        }

        if (is_scalar($this->value) || is_null($this->value)) {
            $this->hasScalarValue = true;

            return $this->scalarValue = $this->value;
        }

        if (is_array($this->value)) {
            foreach ($this->value as $element) {
                if (is_scalar($element)) {
                    $this->hasScalarValue = true;

                    return $this->scalarValue = $element;
                }
            }
        }

        if (is_object($this->value)) {
            $vars = get_object_vars($this->value);

            if (count($vars) > 0 && is_scalar($value = reset($vars))) {
                $this->hasScalarValue = true;

                return $this->scalarValue = $value;
            }
        }

        throw new Exception("Cannot find any scalar value in '{$this->name}' property", self::NO_SCALAR_VALUE);
    }

    private function checkStringLengthConstraint($minLength = null, $maxLength = null)
    {
        if (is_integer($minLength) && is_integer($maxLength) && $minLength > $maxLength) {
            throw new InvalidArgumentException('Maximum length must be greater than minimal length.');
        } elseif (!is_string($this->getScalarValue())) {
            $this->violations[] = 'Must be a string';
        } elseif (is_integer($minLength) && strlen($this->getScalarValue()) < $minLength) {
            $this->violations[] = "Must have a minimal length of {$minLength}";
        } elseif (is_integer($maxLength) && strlen($this->getScalarValue()) > $maxLength) {
            $this->violations[] = "Must have a maximal length of {$maxLength}";
        }
    }
}