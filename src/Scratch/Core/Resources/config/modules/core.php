<?php

return function ($container) {
    return [
        'connection' => function () use ($container) {
            static $connection;

            if (null === $connection) {
                $dbConfig = $container['env'] == 'test' ? $container['config']['testDb'] : $container['config']['db'];

                switch ($dbConfig['driver']) {
                    // Must be PDO for transactions
                    case 'MySQL':
                        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['database']}";
                        $connection = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
                            PDO::ATTR_PERSISTENT => true,
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                        ]);
                        break;
                    default:
                        throw new RuntimeException("Unknown driver '{$dbConfig['driver']}'");
                }
            }

            return $connection;
        },
        'model' => function ($package, $model) use ($container) {
            static $models = [];
            $dbConfig = $container['env'] == 'test' ? $container['config']['testDb'] : $container['config']['db'];
            $namespace = str_replace('/', '\\', $package);
            $class = "{$namespace}\Model\Driver\\{$dbConfig['driver']}\\{$model}";

            if (!isset($models[$class])) {
                if (isset($container['config']['packages'][$package])
                    && $container['config']['packages'][$package] === true) {
                    if (file_exists($container['config']['srcDir']. '/' . str_replace('\\', '/', $class) . '.php')) {
                        $models[$class] = new $class;

                        if ($models[$class] instanceof Scratch\Core\Library\AbstractModel) {
                            $models[$class]->setConnection($container['core::connection']());
                            $models[$class]->setValidator($container['core::validator']());
                            $models[$class] instanceof Scratch\Core\Library\ContainerAwareInterface && $models[$class]->setContainer($container);
                        }
                    } else {
                        throw new Exception("Cannot find the model '{$model}' in package '{$package}' (driver : '{$dbConfig['driver']}').");
                    }
                } else {
                    throw new RuntimeException("Package '{$package}' is not installed or inactive.");
                }
            }

            return $models[$class];
        },
        'security' => function () use ($container) {
            static $security;

            if (null === $security) {
                $security = new Scratch\Core\Library\Security($container['core::model']('Scratch/Core', 'UserModel'));
            }

            return $security;
        },
        'templating' => function () use ($container) {
            static $templating;

            if (null === $templating) {
                $templating = new Scratch\Core\Library\Templating(
                    $container,
                    $_SERVER['SCRIPT_NAME'], // move superglobals in container init (app.php)
                    preg_replace('#/[^/]*$#', '', $_SERVER['SCRIPT_NAME'])
                );
            }

            return $templating;
        },
        'validator' => function () use ($container) {
            static $validator;

            if (null === $validator) {
                $validator = new Scratch\Core\Library\ArrayValidator();
            }

            return $validator;
        },
        'navbar' => function () use ($container) {
            $navbarRenderer = new Scratch\Core\Renderer\NavbarRenderer($container['core::templating']());

            return $navbarRenderer->render();
        },
        'footer' => function () use ($container) {
            $footerRenderer = new Scratch\Core\Renderer\FooterRenderer($container['core::templating']());

            return $footerRenderer->render();
        },
        'masterPage' => function () use ($container) {
            return new Scratch\Core\Library\HtmlPageBuilder(
                $container['core::templating'](),
                __DIR__.'/../../templates/master.html.php'
            );
        }
    ];
};