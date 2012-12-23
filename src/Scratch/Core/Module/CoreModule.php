<?php

namespace Scratch\Core\Module;

use \PDO;
use \Exception;
use Scratch\Core\Library\Module\AbstractModule;
use Scratch\Core\Library\Module\ModuleManager;
use Scratch\Core\Module\Exception\NotFoundException;
use Scratch\Core\Library\AbstractModel;
use Scratch\Core\Library\Security;
use Scratch\Core\Library\Templating;
use Scratch\Core\Library\ArrayValidator;
use Scratch\Core\Renderer\NavbarRenderer;
use Scratch\Core\Renderer\FooterRenderer;
use Scratch\Core\Library\HtmlPageBuilder;

class CoreModule extends AbstractModule
{
    /**
     * Manager used to inject modules into controllers and listeners.
     *
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * Local cache for the parsed routing files.
     *
     * @var array
     */
    private $routeSets = [];

    /**
     * Local cache of the instantiated listeners.
     *
     * @var array
     */
    private $listeners = [];

    private $connection;
    private $models = [];
    private $security;
    private $templating;
    private $validator;

    /**
     * Sets the module manager.
     *
     * @todo throw an exception if called more than once
     *
     * @param Scratch\Core\Library\Module\ModuleManager $manager
     */
    public function setModuleManager(ModuleManager $manager)
    {
        $this->moduleManager = $manager;
    }

    /**
     * Given a path info and an HTTP method, searches for a matching route in the
     * routing files. If the last parameter is set to false, returns whether a
     * route was matched or not. If set to true, executes the matching controller
     * or throws a 404 (not found) exception.
     *
     * @param   string  $pathInfo   The pattern to be matched
     * @param   string  $method     The HTTP method
     * @param   boolean $execute    Whether the controller is to be executed if a route is found
     * @return  mixed               Boolean value if $execute is false, controller return value otherwise
     * @throws  NotFoundException   If $execute is true and no route was found
     */
    public function matchUrl($pathInfo, $method, $execute = true)
    {
        if (preg_match('#^/([^/]*)#', $pathInfo, $prefixMatches)) { // extract prefix
            $routing = $this->definitions['routing'];

            if (isset($routing[$prefix = $prefixMatches[1]])) {
                if (!isset($this->routeSets[$prefix])) {
                    $this->routeSets[$prefix] = require $routing[$prefix]; // load routing file
                }

                if (isset($this->routeSets[$prefix][$method])) {
                    for ($i = strlen($pathInfo) - 1; $i > 1 && $pathInfo[$i] === '/'; $i--) {
                        $pathInfo = substr($pathInfo, 0, $i); // remove path info trailing slashes
                    }

                    foreach ($this->routeSets[$prefix][$method] as $pattern => $controller) {
                        $pattern = $pattern === '/' ? '' : $pattern;

                        if (preg_match("#^/{$prefix}{$pattern}$#", $pathInfo, $paramMatches)) { // check if a route is matched and extract parameters
                            if (!$execute) {
                                return true;
                            }

                            array_shift($paramMatches); // keep parameters only (remove whole string match)
                            $controllerParts = explode('::', $controller);
                            $controller = $this->moduleManager->createConsumer($controllerParts[0]); // create the controller and inject modules

                            return call_user_func_array([$controller, $controllerParts[1]], $paramMatches); // execute the controller
                        }
                    }
                }
            }
        }

        if (!$execute) {
            return false;
        }

        throw new NotFoundException("No matching controller for path '{$pathInfo}' with method '{$method}'");
    }

    /**
     * Dispatches an event to every listener declared in the active packages
     * definitions that is attached to the event name.
     *
     * @param string    $eventName  Name of the event to dispatch
     * @param mixed     $event      Event to dispatch
     */
    public function dispatch($eventName, $event)
    {
        if (isset($this->definitions['listeners'][$eventName])) {
            foreach ($this->definitions['listeners'][$eventName] as $listener) {
                if (!isset($this->listeners[$listener])) {
                    $listenerParts = explode('::', $listener);
                    $listenerInstance = $this->moduleManager->createConsumer($listenerParts[0]);
                    $this->listeners[$listener] = [$listenerInstance, $listenerParts[1]];
                }

                call_user_func([$this->listeners[$listener][0], $this->listeners[$listener][1]], $event);
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