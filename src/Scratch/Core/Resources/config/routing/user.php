<?php

return [
    'GET' => [
        '/form' => 'Scratch\Core\Controller\UserController::creationForm',
        '/test/form' => 'Scratch\Core\Controller\UserController::testForm'
    ],
    'POST' => [
        '/create' => 'Scratch\Core\Controller\UserController::create',
        '/test' => 'Scratch\Core\Controller\UserController::test'
    ]
];