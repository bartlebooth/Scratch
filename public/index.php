<?php

call_user_func(function (array $config, $env, $matchUrl) {
    $requestError = error_get_last();
    $originalObLevel = ob_get_level();
    set_error_handler(function ($level, $msg, $file, $line) {
        throw new Exception("{$msg} in {$file} line {$line} (php error level : {$level})");
    });
    spl_autoload_register(function ($class) use ($config) {
        require_once $config['srcDir'] . '/' . str_replace('\\', '/', $class) . '.php';
    });

    try {
        $routing = [];
        $modules = [];
        $listeners = [];
        $translations = [];

        foreach ($config['packages'] as $package => $isActive) {
            $isActive && require "{$config['srcDir']}/{$package}/Resources/config/definitions.php";
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
                'frontScript' => $_SERVER['SCRIPT_NAME'],
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
        $sessionHandler = new Scratch\Core\Library\SessionHandler($config['sessionDir'], $config['sessionLifetime']);
        session_set_save_handler($sessionHandler);
        session_start();
        $matchUrl && $coreModule->matchUrl(!isset($_SERVER['PATH_INFO']) ? '/' : $_SERVER['PATH_INFO'], $_SERVER['REQUEST_METHOD']);
    } catch (Exception $ex) {
        while (ob_get_level() > $originalObLevel) {
            ob_end_clean();
        }

        $coreModule->dispatch('exception', $ex);
    }
}, isset($config) ? $config : require __DIR__ . '/../config/main.php', isset($env) ? $env : false, isset($matchUrl) ? $matchUrl : true);