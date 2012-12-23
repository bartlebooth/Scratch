<?php

return [
    'GET' => [
        '/foo/(\d+)/bat/([a-z]{3})' => 'Controller2::foo',
        '/bar/(ab|cd)/(\d*)/bat/(\d+)' => 'Controller2::bar'
    ]
];