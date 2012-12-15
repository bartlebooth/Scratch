<?php

call_user_func(function () {
    $requestError = error_get_last();
    set_error_handler(function ($level, $msg, $file, $line) {
        throw new Exception("{$msg} in {$file} line {$line} (php error level : {$level})");
    });
    $config = require_once __DIR__ . '/../config/main.php';
    spl_autoload_register(function ($class) use ($config) {
        require_once $config['srcDir'] . '/' . str_replace('\\', '/', $class) . '.php';
    });

    try {
        $routing = array();
        $modules = array();
        $listeners = array();
        $env = 'prod';

        foreach ($config['packages'] as $package => $isActive) {
            $isActive && require "{$config['srcDir']}/{$package}/Resources/config/definitions.php";
        }

        foreach ($config['devIps'] as $devIp => $isActive) {
            if ($_SERVER['REMOTE_ADDR'] === $devIp && $isActive) {
                $env = 'dev';
                break;
            }
        }

        $container = new Scratch\Core\Library\Container($env, $config, $routing, $modules, $listeners, $requestError);
        register_shutdown_function(function () use ($container) {
            if ((null !== $error = error_get_last()) && $error['type'] === E_ERROR) {
                $container['dispatch']('exception', new RuntimeException(
                    "{$error['message']}, in {$error['file']} line {$error['line']}"
                ));
            }
        });
        $sessionHandler = new Scratch\Core\Library\SessionHandler($config['sessionDir'], $config['sessionLifetime']);
        session_set_save_handler($sessionHandler);
        session_start();
        $container['match'](!isset($_SERVER['PATH_INFO']) ? '/' : $_SERVER['PATH_INFO'], $_SERVER['REQUEST_METHOD']);
    } catch (Exception $ex) {
        $container['dispatch']('exception', $ex);
    }
});
