<?php

call_user_func(function ($config) {
    set_error_handler(function ($level, $msg, $file, $line) {
        throw new Exception("{$msg} in {$file} line {$line} (php error level : {$level})");
    });
    spl_autoload_register(function ($class) use ($config) {
        require_once $config['srcDir'] . '/' . str_replace('\\', '/', $class) . '.php';
    });

    try {
        $routing = array();
        $modules = array();
        $listeners = array();

        foreach ($config['packages'] as $package => $isActive) {
            $isActive && require "{$config['srcDir']}/{$package}/Resources/config/definitions.php";
        }

        $env = 'prod';

        foreach ($config['devIps'] as $devIp => $isActive) {
            if ($_SERVER['REMOTE_ADDR'] === $devIp && $isActive) {
                $env = 'dev';
                break;
            }
        }

        $container = new Scratch\Core\Library\Container($env, $config, $routing, $modules, $listeners);
        register_shutdown_function(function () use ($container) {
            if (null !== $fatalError = error_get_last()) {
                $container['dispatch']('exception', new RuntimeException(
                    "{$fatalError['message']}, in {$fatalError['file']} line {$fatalError['line']}"
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
}, isset($config) ? $config : require_once __DIR__ . '/../config/main.php');