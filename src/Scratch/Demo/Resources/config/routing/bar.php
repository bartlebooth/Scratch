<?php

return [
    'GET' => [
        '/multiParams/first/(\d+)/second/([a-z]+)/third/([a-z]+)?' => 'Scratch\Demo\Controller\BarController::multipleParams'
    ],
];