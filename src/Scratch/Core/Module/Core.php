<?php

namespace Scratch\Core\Module;

use \PDO;
use \Exception;
use Scratch\Core\Library\Module\AbstractModule;
use Scratch\Core\Library\Module\ModuleManager;
use Scratch\Core\Library\AbstractModel;
use Scratch\Core\Library\Security;
use Scratch\Core\Library\Templating;
use Scratch\Core\Library\ArrayValidator;
use Scratch\Core\Renderer\NavbarRenderer;
use Scratch\Core\Renderer\FooterRenderer;
use Scratch\Core\Library\HtmlPageBuilder;

class Core extends AbstractModule
{
    private $moduleManager;
    private $connection;
    private $models = [];
    private $security;
    private $templating;
    private $validator;

    public function setModuleManager(ModuleManager $manager)
    {
        $this->moduleManager = $manager;
    }

    public function matchUrl($pathInfo, $method, $execute = true)
    {
        if (preg_match('#^/([^/]*)#', $pathInfo, $prefixMatches)) {
            $routing = $this->getDefinitions()['routing'];

            if (isset($routing[$prefixMatches[1]])) {
                static $routeSets = [];
                $routeSet = isset($routeSets[$prefixMatches[1]]) ?
                    $routeSets[$prefixMatches[1]] :
                    $routeSets[$prefixMatches[1]] = require $routing[$prefixMatches[1]];

                if (isset($routeSet[$method])) {
                    $pathInfo = preg_replace('#.+(/)$#', substr($pathInfo, 0, strlen($pathInfo) - 1), $pathInfo);

                    foreach ($routeSet[$method] as $pattern => $controller) {
                        if (preg_match("#^/{$prefixMatches[1]}{$pattern}$#", $pathInfo, $paramMatches)) {
                            if (!$execute) {
                                return true;
                            }

                            array_shift($paramMatches);

                            if ($controller instanceof \Closure) {
                                call_user_func_array($controller, $paramMatches);
                            } else {
                                $controllerParts = explode('::', $controller);
                                $controller = $this->moduleManager->createConsumer($controllerParts[0]);

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

        throw new Exception("No matching controller for path '{$pathInfo}' with method '{$method}'", 404);
    }

    public function dispatch($eventName, $event)
    {
        $listeners = $this->getDefinitions()['listeners'];

        if (isset($listeners[$eventName])) {
            foreach ($listeners[$eventName] as $listener) {
                if ($listener instanceof Closure) {
                    call_user_func($listener, $event);
                } else {
                    static $listenerInstances = [];

                    if (!isset($listenerInstances[$listener])) {
                        $listenerParts = explode('::', $listener);
                        $listenerInstance = $this->moduleManager->createConsumer($listenerParts[0]);
                        $listenerInstances[$listener] = [$listenerInstance, $listenerParts[1]];
                    }

                    call_user_func([$listenerInstances[$listener][0], $listenerInstances[$listener][1]], $event);
                }
            }
        }
    }

    public function getConnection()
    {
        if (!isset($this->connection)) {
            $config = $this->getConfiguration();
            $dbConfig = $this->getEnvironment() == 'test' ? $config['testDb'] : $config['db'];

            switch ($dbConfig['driver']) {
                // Must be PDO for transactions
                case 'MySQL':
                    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']}";
                    $this->connection = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
                        PDO::ATTR_PERSISTENT => true,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    ]);
                    break;
                default:
                    throw new Exception("Unknown driver '{$dbConfig['driver']}'");
            }
        }

        return $this->connection;
    }

    public function getModel($package, $model)
    {
        $config = $this->getConfiguration();
        $dbConfig = $this->getEnvironment() == 'test' ? $config['testDb'] : $config['db'];
        $namespace = str_replace('/', '\\', $package);
        $class = "{$namespace}\Model\Driver\\{$dbConfig['driver']}\\{$model}";

        if (!isset($this->models[$class])) {
            if (isset($config['packages'][$package]) && $config['packages'][$package] === true) {
                if (file_exists($config['srcDir']. '/' . str_replace('\\', '/', $class) . '.php')) {
                    $this->models[$class] = new $class;

                    if ($this->models[$class] instanceof AbstractModel) {
                        $this->models[$class]->setConnection($this->getConnection());
                        $this->models[$class]->setValidator($this->getValidator());

                        // must be fixed !!!!!!
                        //$this->models[$class] instanceof Scratch\Core\Library\ContainerAwareInterface && $models[$class]->setContainer($container);
                    }
                } else {
                    throw new Exception("Cannot find the model '{$model}' in package '{$package}' (driver : '{$dbConfig['driver']}').");
                }
            } else {
                throw new Exception("Package '{$package}' is not installed or inactive.");
            }
        }

        return $this->models[$class];
    }

    public function getSecurity()
    {
        if (!isset($this->security)) {
            $this->security = new Security($this->getModel('Scratch/Core', 'UserModel'));
        }

        return $this->security;
    }

    public function getTemplating()
    {
        if (!isset($this->templating)) {
            $this->templating = new Templating(
                //$container, NEED TO BE FIXED !!!!!!!!!!

                $_SERVER['SCRIPT_NAME'], // move superglobals in container init (app.php)
                preg_replace('#/[^/]*$#', '', $_SERVER['SCRIPT_NAME'])
            );
        }

        return $this->templating;
    }

    public function getValidator()
    {
        if (!isset($this->validator)) {
            $this->validator = new ArrayValidator();
        }

        return $this->validator;
    }

    public function getNavbar()
    {
        $navbarRenderer = new NavbarRenderer($this->getTemplating());

        return $navbarRenderer->render();
    }

    public function getFooter()
    {
        $footerRenderer = new FooterRenderer($this->getTemplating());

        return $footerRenderer->render();
    }

    public function getMasterPage()
    {
        return new HtmlPageBuilder(
            $this->getTemplating(),
            __DIR__.'/../../templates/master.html.php' // NEED TO BE FIXED !!!!!!!
        );
    }
}