<?php

return call_user_func(
    function (array $config, $env, $matchUrl, $autoload) {
        $requestError = error_get_last();
        $originalObLevel = ob_get_level();
        set_error_handler(function ($level, $msg, $file, $line) {
            throw new Exception("{$msg} in {$file} line {$line} (php error level : {$level})");
        });
        $autoload && spl_autoload_register(function ($class) use ($config) {
            if (file_exists($file = $config['srcDir'] . '/' . str_replace('\\', '/', $class) . '.php')) {
                require_once $file;
            }
        });

        try {
            $routing = [];
            $modules = [];
            $listeners = [];
            $translations = [];

            foreach ($config['packages'] as $package) {
                $package['isActive'] && require $package['definitions'];
            }

            if (!$env) {
                $env = 'prod';

                foreach ($config['devIps'] as $devIp => $isActive) {
                    if ($_SERVER['REMOTE_ADDR'] === $devIp && $isActive) {
                        $env = 'dev';
                        break;
                    }
                }
            }

            $moduleManager = new Scratch\Core\Library\Module\ModuleManager(
                ['routing' => $routing, 'modules' => $modules, 'listeners' => $listeners, 'translations' => $translations],
                $config,
                [
                    'frontScript' => $env === 'test' ? '' : $_SERVER['SCRIPT_NAME'],
                    'requestError' => $requestError
                ],
                $env
            );
            $coreModule = $moduleManager->getModule('Scratch\Core\Module\CoreModule');
            register_shutdown_function(function () use ($coreModule) {
                if ((null !== $error = error_get_last()) && $error['type'] === E_ERROR) {
                    $coreModule->dispatch('exception', new Exception("{$error['message']}, in {$error['file']} line {$error['line']}"));
                }
            });
            $matchUrl && $coreModule->matchUrl(!isset($_SERVER['PATH_INFO']) ? '/' : $_SERVER['PATH_INFO'], $_SERVER['REQUEST_METHOD']);

            return $moduleManager;
        } catch (Exception $ex) {
            while (ob_get_level() > $originalObLevel) {
                ob_end_clean();
            }

            $coreModule->dispatch('exception', $ex);
        }
    },
    isset($config) ? $config : require __DIR__ . '/../config/main.php',
    isset($env) ? $env : false,
    isset($matchUrl) ? $matchUrl : true,
    isset($autoload) ? $autoload : true
);