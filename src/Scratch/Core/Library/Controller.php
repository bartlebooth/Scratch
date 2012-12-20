<?php

namespace Scratch\Core\Library;

use \Exception;

class Controller implements ContainerAwareInterface
{
    /** @var Container */
    protected $container;

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function getPostedData()
    {
        if (false !== $requestError = $this->container['requestError']) {
            if (preg_match('#POST Content-Length of \d+ bytes exceeds the limit of \d+ bytes#', $requestError['message'])) {
                throw new PostLimitException();
            }

            throw new Exception($requestError['message']);
        }

        return array_merge($this->trimData($_POST), $_FILES);
    }

    private function trimData(array $data)
    {
        $properties = [];

        foreach ($data as $property => $value) {
            if (is_string($value)) {
                $properties[$property] = trim($value);
            } elseif (is_array($value)) {
                $properties[$property] = $this->trimData($value);
            }
        }

        return $properties;
    }
}