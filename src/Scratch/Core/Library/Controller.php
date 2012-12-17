<?php

namespace Scratch\Core\Library;

use \Exception;

class Controller extends ContainerAware
{
    public function throwExceptionOnRequestError()
    {
        if (false !== $requestError = $this->container['requestError']) {
            if (preg_match('#POST Content-Length of \d+ bytes exceeds the limit of \d+ bytes#', $requestError['message'])) {
                throw new PostLimitException();
            }

            throw new Exception($requestError['message']);
        }
    }

    public function getPostedData()
    {
        if (false !== $requestError = $this->container['requestError']) {
            if (preg_match('#POST Content-Length of \d+ bytes exceeds the limit of \d+ bytes#', $requestError['message'])) {
                throw new PostLimitException();
            }

            throw new Exception($requestError['message']);
        }

        return array_merge($this->filter($_POST), $_FILES);
    }

    private function filter(array $data)
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