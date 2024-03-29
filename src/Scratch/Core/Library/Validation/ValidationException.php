<?php

namespace Scratch\Core\Library\Validation;

class ValidationException extends \Exception
{
    private $violations;

    public function setViolations(array $violations)
    {
        $this->violations = $violations;
    }

    public function getViolations()
    {
        return $this->violations;
    }
}