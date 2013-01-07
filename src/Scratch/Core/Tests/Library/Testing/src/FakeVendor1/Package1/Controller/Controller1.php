<?php

namespace FakeVendor1\Package1\Controller;

class Controller1
{
    public function action1()
    {
        http_response_code(200);
        echo 'FakeVendor1\Package1\Controller1::action1 output';
    }

    public function action2()
    {
        echo '<div id="bar"><span>foo</span></div>';
    }
}