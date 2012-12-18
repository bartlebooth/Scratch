<?php

namespace Scratch\Core\Renderer;

use Scratch\Core\Library\Templating;

class FooterRenderer
{
    private $templating;

    public function  __construct(Templating $templating)
    {
        $this->templating = $templating;
    }

    public function render()
    {
        return $this->templating->display(__DIR__ . '/../Resources/templates/footer.html.php');
    }
}