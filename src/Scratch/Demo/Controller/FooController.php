<?php

namespace Scratch\Demo\Controller;

use Scratch\Core\Library\Controller;

class FooController extends Controller
{
    public function index()
    {
        //$this->container['core::session']();
        var_dump($_SESSION);
        $_SESSION = [];
        //session_write_close();
        $templating = $this->container['core::templating']();
        $templating->render(
            __DIR__ . '/../../Core/Resources/templates/master.html.php',
            [
                'sectionTitle' => 'Foo',
                'body' => $templating->render(
                    __DIR__ . '/../Resources/templates/foo.html.php',
                    ['foo' => 123, 'bar' => 456],
                    false
                )
            ]
        );
        /*
        $this->container['dispatch']('eventFoo', 'BOUM');
        var_dump($this->container['moduleFoo::boum']());
        echo 'Demo\Controller\FooController::index';*/
    }

    public function requiredDigitParam($param)
    {
        echo 'Demo\Controller\FooController::requiredDigitParam with ' . $param;
    }

    public function optionalStringParam($param = 'default value')
    {
        echo 'Demo\Controller\FooController::optionalStringParam with ' . $param;
    }

    public function postOnly()
    {
        echo 'Demo\Controller\FooController::postOnly';
    }
}