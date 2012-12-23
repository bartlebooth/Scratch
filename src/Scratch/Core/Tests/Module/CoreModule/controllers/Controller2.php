<?php

class Controller2
{
    public function foo($x, $y)
    {
        echo 'Foo with x = ' . $x . ' and y = ' . $y;
    }

    public function bar($x, $y, $z)
    {
        $y = empty($y) ? 'default-y' : $y;

        echo 'Bar with x = ' . $x . ', y = ' . $y . ' and z = ' . $z;
    }
}