<?php

namespace FakeVendor1\Package3\Controller;

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
        echo sprintf(
            '<div><a href="%s">Link</a><a href="#">Anchor</a></div>',
            $this->core->getContext()['frontScript'] . '/prefix3/action2'
        );
    }

    public function action2()
    {
        echo 'Output of /prefix3/action2 (link target of /prefix3/action1)';
    }

    public function action3()
    {
        echo '<div><div><a href="/foo/bar">Some link</a></div></div>';
    }

    public function action4()
    {
        echo '<div>Some div</div>';
    }

    public function action5()
    {
        echo '<div><a id="no-href-anchor">Invalid link</a></div>';
    }
}