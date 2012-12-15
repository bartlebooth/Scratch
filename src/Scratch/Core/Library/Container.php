<?php

namespace Scratch\Core\Library;

use \ArrayAccess;
use \Closure;
use \RuntimeException;

class Container implements ArrayAccess
{
    private $modules;
    private $services;

    public function __construct($env, array $config, array $routing, array $modules, array $listeners, $requestError = null) {
        $this->modules = $modules;
        $container = $this;
        $this->services = [
            'env' => $env,
            'config' => $config,
            'requestError' => $requestError ?: false,
            'dispatch' => function ($eventName, $event) use ($listeners, $container) {
                if (isset($listeners[$eventName])) {
                    foreach ($listeners[$eventName] as $listener) {
                        if ($listener instanceof Closure) {
                            call_user_func($listener, $event);
                        } else {
                            static $listenerInstances = [];

                            if (!isset($listenerInstances[$listener])) {
                                $listenerParts = explode('::', $listener);
                                $listenerInstance = new $listenerParts[0];
                                $listenerInstance instanceof ContainerAware && $listenerInstance->setContainer($container);
                                $listenerInstances[$listener] = [$listenerInstance, $listenerParts[1]];
                            }

                            call_user_func([$listenerInstances[$listener][0], $listenerInstances[$listener][1]], $event);
                        }
                    }
                }
            },
            'match' => function ($pathInfo, $method, $execute = true) use ($routing, $container) {
                if (preg_match('#^/([^/]*)#', $pathInfo, $prefixMatches)) {
                    if (isset($routing[$prefixMatches[1]])) {
                        static $routeSets = [];
                        isset($routeSets[$prefixMatches[1]]) ?
                            $routeSet = $routeSets[$prefixMatches[1]] :
                            $routeSet = $routeSets[$prefixMatches[1]] = require $routing[$prefixMatches[1]];

                        if (isset($routeSet[$method])) {
                            $pathInfo = preg_replace('#.+(/)$#', substr($pathInfo, 0, strlen($pathInfo) - 1), $pathInfo);

                            foreach ($routeSet[$method] as $pattern => $controller) {
                                if (preg_match("#^/{$prefixMatches[1]}{$pattern}$#", $pathInfo, $paramMatches)) {
                                    if (!$execute) {
                                        return true;
                                    }

                                    array_shift($paramMatches);

                                    if ($controller instanceof Closure) {
                                        call_user_func_array($controller, $paramMatches);
                                    } else {
                                        $controllerParts = explode('::', $controller);
                                        $controller = new $controllerParts[0];
                                        $controller instanceof ContainerAware && $controller->setContainer($container);

                                        return call_user_func_array([$controller, $controllerParts[1]], $paramMatches);
                                    }
                                }
                            }
                        }
                    }
                }

                if (!$execute) {
                    return false;
                }

                throw new RuntimeException("No matching controller for path '{$pathInfo}' with method '{$method}'", 404);
            }
        ];
    }

    public function offsetGet($offset)
    {
        if (isset($this->services[$offset])) {
            return $this->services[$offset];
        }

        if (count($nameParts = explode('::', $offset)) == 2) {
            static $parsedModules = [];

            if (!isset($parsedModules[$nameParts[0]]) && isset($this->modules[$nameParts[0]])) {
                $parsedModules[$nameParts[0]] = call_user_func(require $this->modules[$nameParts[0]], $this);
            }

            if (isset($parsedModules[$nameParts[0]][$nameParts[1]])) {
                return $this->services[$offset] = $parsedModules[$nameParts[0]][$nameParts[1]];
            }
        }

        throw new RuntimeException("Unknown parameter or service '{$offset}'");
    }

    public function offsetExists($offset)
    {
        return isset($this->services[$offset]);
    }

    public function offsetSet($offset, $value)
    {
        throw new RuntimeException("Cannot set parameter or service '{$offset}' : container is read-only.", 500);
    }

    public function offsetUnset($offset)
    {
        throw new RuntimeException("Cannot unset parameter or service '{$offset}' : container is read-only.", 500);
    }
}