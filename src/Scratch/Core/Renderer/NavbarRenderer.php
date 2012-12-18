<?php

namespace Scratch\Core\Renderer;

use Scratch\Core\Library\Templating;

class NavbarRenderer
{
    private $templating;

    public function __construct(Templating $templating)
    {
        $this->templating = $templating;
    }

    public function render()
    {
        return $this->templating->display(__DIR__ . '/../Resources/templates/navbar.html.php');
    }
}