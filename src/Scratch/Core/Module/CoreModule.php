<?php

namespace Scratch\Core\Module;

use \PDO;
use Scratch\Core\Library\Module\AbstractModule;
use Scratch\Core\Library\Module\ModuleManager;
use Scratch\Core\Library\Module\Exception\ParametersAlreadySetException;
use Scratch\Core\Module\Exception\NotFoundException;
use Scratch\Core\Module\Exception\UnknownDriverException;
use Scratch\Core\Module\Exception\UnknownPackageException;
use Scratch\Core\Module\Exception\UnloadableModelException;
use Scratch\Core\Library\SessionHandler;
use Scratch\Core\Library\Security;
use Scratch\Core\Library\Validation\ArrayValidator;
use Scratch\Core\Library\Templating\Templating;
use Scratch\Core\Library\RendererInterface;
use Scratch\Core\Module\Exception\UnexpectedRendererTypeException;

use Scratch\Core\Library\HtmlPageBuilder;

/**
 * Module providing the core services of the platform. It is built like any other
 * module, but has a reference to the module manager which allows it to inject
 * modules into instances of objects if needed (e.g. controllers, listeners, models
 * or renderers that implement the ModuleConsumerInterface).
 */
class CoreModule extends AbstractModule
{
    /**
     * Manager used to inject modules into ModuleConsumerInterface instances.
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

    /**
     * Database connection instance.
     *
     * @var PDO
     */
    private $connection;

    /**
     * Local cache of the instantiated models.
     *
     * @var array
     */
    private $models = [];

    /**
     * Security class instance.
     *
     * @var Scratch\Core\Library\Security
     */
    private $security;

    /**
     * Array validator instance.
     *
     * @var Scratch\Core\Library\Validation\ArrayValidator
     */
    private $validator;

    /**
     * Templating engine instance.
     *
     * @var Scratch\Core\Library\Templating\Templating
     */
    private $templating;

    /**
     * Sets the module manager. This method is intended to be called once by
     * the module manager. Other calls will throw an exception.
     *
     * @param Scratch\Core\Library\Module\ModuleManager $manager
     */
    public function setModuleManager(ModuleManager $manager)
    {
        if (isset($this->moduleManager)) {
            throw new ParametersAlreadySetException('Module manager can only be set once');
        }

        $this->moduleManager = $manager;
    }

    /**
     * Given a path info and an HTTP method, searches for a matching route in the
     * routing files. If the last parameter is set to false, returns whether a
     * route was matched or not. If set to true, executes the matching controller
     * or throws a 404 (not found) exception. If the controller implements the
     * interface ModuleConsumerInterface, the modules it depends on are injected
     * into it.
     *
     * @param   string  $pathInfo   The pattern to be matched
     * @param   string  $method     The HTTP method
     * @param   boolean $execute    Whether the controller is to be executed if a route is found
     * @return  mixed               Boolean value if $execute is false, controller return value otherwise
     * @throws  NotFoundException   if $execute is true and no route was found
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
     * Dispatches an event to all the listeners declared in the active packages
     * definitions that are attached to the event name. If the listeners implement
     * the interface ModuleConsumerInterface, the modules they depends on are
     * injected into them.
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

    /**
     * Returns a PDO connection according to the database parameters declared in
     * the main configuration file.
     *
     * @return PDO
     * @throws UnknownDriverException if the driver is not supported
     */
    public function getConnection()
    {
        if (!isset($this->connection)) {
            $dbConfig = $this->configuration[$this->environment === 'test' ? 'testDb' : 'db'];

            switch ($dbConfig['driver']) {
                case 'MySQL':
                    $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']}";
                    $this->connection = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
                        PDO::ATTR_PERSISTENT => true,
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                    ]);
                    break;
                default:
                    throw new UnknownDriverException("Unknown driver '{$dbConfig['driver']}'");
            }
        }

        return $this->connection;
    }

    /**
     * Returns an instance of a model class according to the database driver declared
     * in the main configuration file. If the model implements the interface
     * ModuleConsumerInterface, the modules it depends on are injected into it.
     *
     * @param string $package   The package in which the model is defined (e.g. 'VendorX/PackageY')
     * @param string $model     The name of the model class
     * @return object
     * @throws UnknownPackageException  if the package is unknown or inactive
     * @throws UnloadableModelException if the model class cannot be loaded
     */
    public function getModel($package, $model)
    {
        $dbConfig = $this->configuration[$this->environment === 'test' ? 'testDb' : 'db'];
        $namespace = str_replace('/', '\\', $package);
        $class = "{$namespace}\Model\Driver\\{$dbConfig['driver']}\\{$model}";

        if (!isset($this->models[$class])) {
            if (isset($this->configuration['packages'][$package]) && $this->configuration['packages'][$package] === true) {
                if (file_exists($this->configuration['srcDir']. '/' . str_replace('\\', '/', $class) . '.php')) {
                    $this->models[$class] = $this->moduleManager->createConsumer($class);
                } else {
                    throw new UnloadableModelException("Cannot find the model '{$model}' in package '{$package}' (driver : '{$dbConfig['driver']}').");
                }
            } else {
                throw new UnknownPackageException("Package '{$package}' is not installed or inactive.");
            }
        }

        return $this->models[$class];
    }

    /**
     * Starts the session if not started yet.
     */
    public function useSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $sessionHandler = new SessionHandler($this->configuration['sessionDir'], $this->configuration['sessionLifetime']);
            session_set_save_handler($sessionHandler);
            session_start();
        }
    }

    /**
     * Destroys the session if started.
     */
    public function destroySession()
    {
        $this->useSession();
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Returns an instance of the Security class.
     *
     * @return Scratch\Core\Library\Security
     */
    public function getSecurity()
    {
        if (!isset($this->security)) {
            $this->security = new Security($this);
        }

        return $this->security;
    }

    /**
     * Returns an instance of the ArrayValidator class.
     *
     * @return Scratch\Core\Library\Validation\ArrayValidator
     */
    public function getValidator()
    {
        if (!isset($this->validator)) {
            $this->validator = new ArrayValidator();
        }

        return $this->validator;
    }

    /**
     * Returns an instance of the Templating class.
     *
     * @return Scratch\Core\Library\Templating\Templating
     */
    public function getTemplating()
    {
        if (!isset($this->templating)) {
            $this->templating = new Templating($this);
        }

        return $this->templating;
    }

    /**
     * Returns an instance of a renderer. If the renderer implements the interface
     * ModuleConsumerInterface, the modules it depends on are injected into it.
     *
     * @param string $rendererFqcn FQCN of the renderer
     * @returns Scratch\Core\Library\RendererInterface
     * @throws UnexpectedRendererTypeException if the renderer doesn't implement the RendererInterface
     */
    public function getRenderer($rendererFqcn)
    {
        $renderer = $this->moduleManager->createConsumer($rendererFqcn);

        if (!$renderer instanceof RendererInterface) {
            throw new UnexpectedRendererTypeException("Renderer '{$rendererFqcn}' must implement the RendererInterface");
        }

        return $renderer;
    }

    public function getMasterPage()
    {
        return new HtmlPageBuilder(
            $this->getTemplating(),
            __DIR__.'/../../templates/master.html.php' // NEED TO BE FIXED !!!!!!!
        );
    }
}