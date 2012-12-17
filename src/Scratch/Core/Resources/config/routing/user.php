<?php

return [
    'GET' => [
        '/form' => 'Scratch\Core\Controller\UserController::creationForm'
    ],
    'POST' => [
        '/create' => 'Scratch\Core\Controller\UserController::create'
    ],


    'GET' => [
        '/test/form' => 'Scratch\Core\Controller\UserController::testForm'
    ],
    'POST' => [
        '/test' => 'Scratch\Core\Controller\UserController::test'
    ]
];