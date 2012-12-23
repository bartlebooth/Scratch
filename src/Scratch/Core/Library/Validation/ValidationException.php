<?php

namespace Scratch\Core\Library;

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