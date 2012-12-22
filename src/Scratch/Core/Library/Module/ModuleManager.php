<?php

namespace Scratch\Core\Library\Module;

use \ReflectionClass;
use \ReflectionException;
use Scratch\Core\Library\Module\Exception\UnknownModuleException;
use Scratch\Core\Library\Module\Exception\UnloadableModuleException;
use Scratch\Core\Library\Module\Exception\InvalidModuleClassException;
use Scratch\Core\Library\Module\Exception\InvalidDependenciesDeclarationException;
use Scratch\Core\Module\Core;

class ModuleManager
{
    const MODULE_CLASS = 'Scratch\Core\Library\Module\AbstractModule';
    const MODULE_CONSUMER_INTERFACE = 'Scratch\Core\Library\Module\ModuleConsumerInterface';

    private $definitions;
    private $configuration;
    private $environment;
    private $modules;

    public function __construct(array $definitions, array $configuration, $environment)
    {
        $this->definitions = $definitions;
        $this->configuration = $configuration;
        $this->environment = $environment;
        $this->modules = [];
    }

    public function getModule($moduleFqcn)
    {
        if (!isset($this->modules[$moduleFqcn])) {
            if (!in_array($moduleFqcn, $this->definitions['modules'])) {
                throw new UnknownModuleException(sprintf('"%s" is not defined in any active package', $moduleFqcn));
            }

            try {
                $rModule = new ReflectionClass($moduleFqcn);
            } catch (ReflectionException $ex) {
                throw new UnloadableModuleException(sprintf('Module class "%s" is not loadable', $moduleFqcn));
            }

            if (!$rModule->isSubclassOf(self::MODULE_CLASS)) {
                throw new InvalidModuleClassException(
                    sprintf('Module "%s" does not extend %s', $moduleFqcn, self::MODULE_CLASS)
                );
            }
            $this->modules[$moduleFqcn] = $this->doInjectModulesInto($rModule);
            $this->modules[$moduleFqcn]->setApplicationParameters($this->definitions, $this->configuration, $this->environment);
            $this->modules[$moduleFqcn] instanceof Core && $this->modules[$moduleFqcn]->setModuleManager($this);
        }

        return $this->modules[$moduleFqcn];
    }

    public function createConsumer($consumerFqcn)
    {
        return $this->doInjectModulesInto(new ReflectionClass($consumerFqcn));
    }

    private function doInjectModulesInto(ReflectionClass $consumer)
    {
        $modules = [];

        if ($consumer->implementsInterface(self::MODULE_CONSUMER_INTERFACE)) {
            if (null !== $constructor = $consumer->getConstructor()) {
                foreach ($constructor->getParameters() as $parameter) {
                    if (null !== $moduleFqcn = $parameter->getClass()) {
                        $modules[] = $this->getModule($moduleFqcn->name);
                    } else {
                        throw new InvalidDependenciesDeclarationException(
                            sprintf('%s::__construct() does not use type hinting for its module arguments', $consumer->getName())
                        );
                    }
                }
            }
        }

        return $consumer->newInstanceArgs($modules);
    }
}