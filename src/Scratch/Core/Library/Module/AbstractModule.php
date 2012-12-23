<?php

namespace Scratch\Core\Library\Module;

use Scratch\Core\Library\Module\Exception\ParametersAlreadySetException;

/**
 * Base module class.
 */
abstract class AbstractModule
{
    /**
     * Collection of definitions provided by active packages.
     *
     * @var array
     */
    protected $definitions = [];

    /**
     * Main configuration of the application.
     *
     * @var array
     */
    protected $configuration;

    /**
     * Environment of the application.
     *
     * @var string
     */
    protected $environment;

    /**
     * Flag preventing parameters overriding.
     *
     * @var boolean
     */
    private $areParametersSet = false;

    /**
     * Sets the application parameters. This method is intended to be called once by
     * the module manager. Other calls will throw an exception.
     *
     * @param   array   $definitions    Collection of definitions provided by the active packages
     * @param   array   $configuration  Main configuration of the application
     * @param   string  $environment    Environment of the application
     * @throws  ParametersAlreadySetException if the application parameters are already set
     */
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

    /**
     * Returns the definitions provided by the active packages.
     *
     * @return array
     */
    final public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Returns the main configuration of the application.
     *
     * @return array
     */
    final public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Returns the environment of the application.
     *
     * @return string
     */
    final public function getEnvironment()
    {
        return $this->environment;
    }
}