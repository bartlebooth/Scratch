<?php

namespace Scratch\Demo\Controller;

class BarController
{
    public function  multipleParams($param1, $param2, $param3 = 'default value')
    {
        echo 'Demo\Controller\BarController::multipleParams with '
            . 'param1 = ' . $param1
            . 'param2 = ' . $param2
            . 'param3 = ' . $param3;
    }
}