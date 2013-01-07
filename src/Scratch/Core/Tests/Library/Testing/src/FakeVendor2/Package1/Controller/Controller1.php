<?php

namespace FakeVendor2\Package1\Controller;

use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;

class Controller1 implements ModuleConsumerInterface
{
    private $core;

    public function __construct(CoreModule $core)
    {
        $this->core = $core;
    }

    public function action1()
    {
        echo $this->core->getTemplating()->render(__DIR__ . '/../Resources/templates/valid_form1.html.php');
    }

    public function action2()
    {
        printf('Valid form 1 submitted with foo = %s and bar = %s', $_POST['foo'], $_POST['bar']);
    }

    public function action3()
    {
        echo '<div id="not-a-form">This is not a form</div>';
    }

    public function action4()
    {
        echo '<form class="missing-action-form"></form>';
    }
}