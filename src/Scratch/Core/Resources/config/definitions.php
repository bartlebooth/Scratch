<?php

$routing[''] = __DIR__ . '/routing/index.php';
$routing['login'] = __DIR__ . '/routing/login.php';
$routing['logout'] = __DIR__ . '/routing/logout.php';
$routing['user'] = __DIR__ . '/routing/user.php';

$modules['core'] = __DIR__ . '/modules/core.php';

$listeners['exception'][] = 'Scratch\Core\Listener\ExceptionListener::onException';
