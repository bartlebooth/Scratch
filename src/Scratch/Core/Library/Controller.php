<?php

namespace Scratch\Core\Library;

class Controller extends ContainerAware
{
    public function filter(array $data)
    {
        $properties = [];

        foreach ($data as $property => $value) {
            if (is_string($value)) {
                if ('' !== $value = trim($value)) {
                    $properties[$property] = $value;
                }
            } elseif (is_array($value)) {
                if (count($value) > 0) {
                    $properties[$property] = $this->filter($value);
                }
            }
        }

        return $properties;
    }
}