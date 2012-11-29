<?php

return [
    'GET' => [
        '/index' => 'Scratch\Demo\Controller\FooController::index',
        '/required/(\d+)' => 'Scratch\Demo\Controller\FooController::requiredDigitParam',
        '/optional/([a-zA-Z]*)' => 'Scratch\Demo\Controller\FooController::optionalStringParam'
    ],
    'POST' => [
        '/post' => 'Scratch\Demo\Controller\FooController::postOnly'
    ]
];