<?php

namespace Scratch\Core\Library;

class Controller extends ContainerAware
{
    public function filter(array $data)
    {
        // remove null/empty values

        $trimString = function ($value) {
            return ($trimmedValue = trim($value)) === '' ? null : $trimmedValue;
        };

        foreach ($data as $property => $value) {
            if (is_string($value)) {
                $properties[$property] = $trimString($value);
            } elseif (is_array($value)) {
                $properties[$property] = $this->trim($value);
            }
        }

        return $properties;
    }
}