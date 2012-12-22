<?php

namespace Scratch\Core\Library\Module;

use \ReflectionClass;
use \ReflectionException;
use Scratch\Core\Library\Module\Exception\UnknownModuleException;
use Scratch\Core\Library\Module\Exception\UnloadableModuleException;
use Scratch\Core\Library\Module\Exception\MissingModuleInterfaceException;
use Scratch\Core\Library\Module\Exception\InvalidDependenciesDeclarationException;

class ModuleProvider
{
    const MODULE_INTERFACE = 'Scratch\Core\Library\Module\ModuleInterface';
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

            if (!$rModule->implementsInterface(self::MODULE_INTERFACE)) {
                throw new MissingModuleInterfaceException(
                    sprintf('Module "%s" does not implement %s', $moduleFqcn, self::MODULE_INTERFACE)
                );
            }

            $moduleDependencies = [];

            if ($rModule->implementsInterface(self::MODULE_CONSUMER_INTERFACE)) {
                if (!is_array($consumedModules = $moduleFqcn::getConsumedModules())) {
                    throw new InvalidDependenciesDeclarationException(
                        sprintf('Module "%s::getConsumedModules()" must return an array', $moduleFqcn)
                    );
                }

                foreach ($consumedModules as $consumedModule) {
                    $moduleDependencies[] = $this->getModule($consumedModule);
                }
            }

            $this->modules[$moduleFqcn] = $rModule->newInstanceArgs($moduleDependencies);
            $this->modules[$moduleFqcn]->setApplicationParameters($this->definitions, $this->configuration, $this->environment);
        }

        return $this->modules[$moduleFqcn];
    }
}