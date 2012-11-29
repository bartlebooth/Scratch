<?php

return [
    'GET' => [
        '/form' => 'Scratch\Core\Controller\AuthenticationController::loginForm'
    ],
    'POST' => [
        '' => 'Scratch\Core\Controller\AuthenticationController::login'
    ]
];