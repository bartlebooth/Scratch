<?php

namespace Scratch\Core\Library\Module;

use Scratch\Core\Library\Module\Exception\ParametersAlreadySetException;

abstract class AbstractModule
{
    private $definitions;
    private $configuration;
    private $environment;
    private $areParametersSet = false;

    final public function setApplicationParameters(array $definitions, array $configuration, $environment)
    {
        if ($this->areParametersSet) {
            throw new ParametersAlreadySetException('Application parameters can only be set once');
        }

        $this->definitions = $definitions;
        $this->configuration = $configuration;
        $this->environment = $environment;
        $this->areParametersSet = true;
    }

    public function getDefinitions()
    {
        return $this->definitions;
    }

    public function getConfiguration()
    {
        return $this->configuration;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }
}