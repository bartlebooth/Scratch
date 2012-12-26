<?php

namespace Scratch\Core\Library\Validation;

use \Exception;
use \LogicException;
use \InvalidArgumentException;
use \Closure;

class ArrayProperty
{
    const INVALID_VALUE_TYPE = 20;
    const INVALID_VALUE_COUNT = 21;
    const INVALID_FILE_DATA = 22;
    const NO_SCALAR_VALUE = 23;

    private $name;
    private $value;
    private $hasScalarValue;
    private $scalarValue;
    private $ignoreConstraints;
    private $violations;

    public function __construct($name, $value, $ignoreConstraints = false, $notBlankViolation = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->hasScalarValue = false;
        $this->ignoreConstraints = $ignoreConstraints;
        $this->violations = [];

        if ($notBlankViolation && !$ignoreConstraints) {
            throw new LogicException('Constraints must be ignored if the property is blank.');
        }

        $notBlankViolation && ($this->violations[] = 'This field is mandatory');
    }

    public function getName()
    {
        return $this->name;
    }

    public function getValue($forceScalar = false)
    {
        if ($forceScalar) {
            return $this->getScalarValue();
        }

        return $this->value;
    }

    public function getViolations()
    {
        return $this->violations;
    }

    public function toBeString($minLength = null, $maxLength = null)
    {
        if ($this->ignoreConstraints) {
            return $this;
        }

        $this->checkStringLengthConstraint($minLength, $maxLength);

        return $this;
    }

    public function toBeAlphanumeric($minLength = null, $maxLength = null)
    {
        if ($this->ignoreConstraints) {
            return $this;
        }

        $this->checkStringLengthConstraint($minLength, $maxLength);

        if (0 === preg_match('#^[a-zA-Z0-9]+$#', $this->getScalarValue())) {
            $this->violations[] = 'Must be alphanumeric';
        }

        return $this;
    }

    public function toMatch($pattern, $errorMessage)
    {
        if ($this->ignoreConstraints) {
            return $this;
        }

        if (0 === preg_match($pattern, $this->getScalarValue())) {
            $this->violations[] = $errorMessage;
        }

        return $this;
    }

    public function toBeConfirmed()
    {
        if ($this->ignoreConstraints) {
            return $this;
        }

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
        if ($this->ignoreConstraints) {
            return $this;
        }

        if (true !== $isUnique($this->getScalarValue())) {
            $this->violations[] = 'Already used';
        }

        return $this;
    }

    public function toBeEmail()
    {
        if ($this->ignoreConstraints) {
            return $this;
        }

        return $this->toMatch('#^[A-Z0-9_%\+-]+@[A-Z0-9\.-]+\.[A-Z]{2,6}$#i', 'Email address is not valid');
    }

    // File = array
    // Mandatory keys: name, type, size, tmp_name
    // Optional: error
    public function toBeFile($maxSize = null, array $allowedMimeTypes = [])
    {
        if ($this->ignoreConstraints) {
            return $this;
        }

        if (!is_array($this->value)) {
            throw new Exception('Constraint unapplicable : property must be an array of values.', self::INVALID_VALUE_TYPE);
        }

        if (!isset($this->value['name']) || !isset($this->value['type']) || !isset($this->value['size']) || !isset($this->value['tmp_name'])) {
            throw new Exception(
                'Constraint unapplicable : array file property must have the key "name", "type", "size" and "tmp_name".',
                self::INVALID_FILE_DATA
            );
        }

        if (isset($this->value['error'])) {
            if ($this->value['error'] !== UPLOAD_ERR_OK) {
                switch ($this->value['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        return $this->violations[] = 'File is too large';
                    case UPLOAD_ERR_NO_FILE:
                        return $this->violations[] = 'This field is mandatory';
                    case UPLOAD_ERR_PARTIAL:
                        return $this->violations[] = 'Upload error';
                    case UPLOAD_ERR_NO_TMP_DIR:
                        return $this->violations[] = 'Server error (no tmp dir)';
                    case UPLOAD_ERR_CANT_WRITE:
                        return $this->violations[] = 'Server error (cannot write)';
                    case UPLOAD_ERR_EXTENSION:
                        return $this->violations[] = 'Server error (extension error)';
                }
            }
        }

        if (null !== $maxSize && $this->value['size'] > $maxSize) {
            $this->violations[] = 'File is too large';
        }

        if (count($allowedMimeTypes) > 0 && !in_array($this->value['type'], $allowedMimeTypes)) {
            $this->violations[] = 'Not allowed mime type';
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