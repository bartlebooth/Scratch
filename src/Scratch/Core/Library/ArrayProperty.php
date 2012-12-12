<?php

namespace Scratch\Core\Library;

class ArrayProperty
{
    private $name;
    private $value;
    private $ignoreConstraints;
    private $violations;

    public function __construct($name, $value, $ignoreConstraints = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->ignoreConstraints = $ignoreConstraints;
        $this->violations = [];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getViolations()
    {
        if ($this->ignoreConstraints) {
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

        if (0 === preg_match('#^[a-zA-Z0-9]+$#', $this->value)) {
            return $this->violations[] = 'Must be alphanumeric.';
        }

        return $this;
    }

    public function toMatch($pattern, $errorMessage)
    {
        if (0 === preg_match($pattern, $this->value)) {
            $this->violations[] = $errorMessage;
        }

        return $this;
    }

    /*
    public function toBeConfirmed()
    {
        if (!$this->hasCurrentProperty) {
            throw new RuntimeException('Constraint unapplicable : no current property.', self::NO_PROPERTY_SELECTED);
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

    public function toBeUnique(\Closure $isUnique)
    {
        if (!$this->hasCurrentProperty) {
            throw new RuntimeException('Constraint unapplicable : no current property.', self::NO_PROPERTY_SELECTED);
        }

        if (true !== $isUnique($this->currentPropertyValue)) {
            return $this->addViolation($this->currentProperty, 'Already used.');
        }

        return true;
    }
    */

    private function checkStringLengthConstraint($minLength = null, $maxLength = null)
    {
        if (is_integer($minLength) && is_integer($maxLength) && $minLength > $maxLength) {
            throw new Exception('Maximum length must be greater than minimal length.');
        } elseif (!is_string($this->value)) {
            $this->violations[] = 'Must be a string';
        } elseif (is_integer($minLength) && strlen($this->value) < $minLength) {
            $this->violations[] = "Must have a minimal length of {$minLength}";
        } elseif (is_integer($maxLength) && strlen($this->value) > $maxLength) {
            $this->violations[] = "Must have a maximal length of {$maxLength}";
        }
    }
}