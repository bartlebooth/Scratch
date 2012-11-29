<?php

call_user_func(function () {
    $config = require_once __DIR__ . '/../main.php';

    spl_autoload_register(function ($class) use ($config) {
        if (file_exists($file = $config['srcDir'] . '/' . str_replace('\\', '/', $class) . '.php')) {
            require_once $file;
        }
    });
});