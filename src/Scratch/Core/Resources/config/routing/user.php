<?php

return [
    'GET' => [
        '/form' => 'Scratch\Core\Controller\UserController::creationForm'
    ],
    'POST' => [
        '/create' => 'Scratch\Core\Controller\UserController::create'
    ]
];