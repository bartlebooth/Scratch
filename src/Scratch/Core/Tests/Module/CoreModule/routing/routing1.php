<?php

return [
    'GET' => [
        '/' => 'Controller1::foo',
        // next one can only be matched if the prefix is not empty, otherwise 'bar' is
        // interpreted as the prefix and the matcher tries to load a 'bar' routing config
        '/bar' => 'Controller1::bar'
    ]
];