<?php

$routing['prefixFoo'] = __DIR__ . '/routing/foo.php';
$routing['prefixBar'] = __DIR__ . '/routing/bar.php';

$modules['moduleFoo'] = __DIR__ . '/modules/foo.php';

$listeners['eventFoo'][] = 'Scratch\Demo\Listener\FooListener::onFoo';
$listeners['eventFoo'][] = 'Scratch\Demo\Listener\BarListener::onFoo';
$listeners['eventBar'][] = 'Scratch\Demo\Listener\BarListener::onBar';