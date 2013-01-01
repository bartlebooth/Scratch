<?php

namespace Scratch\Core\Renderer;

use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Library\RendererInterface;
use Scratch\Core\Module\CoreModule;

class NavbarRenderer implements RendererInterface, ModuleConsumerInterface
{
    private $templating;

    public function __construct(CoreModule $coreModule)
    {
        $this->templating = $coreModule->getTemplating();
    }

    public function render(array $variables = [])
    {
        echo $this->templating->render(__DIR__ . '/../Resources/templates/navbar.html.php');
    }
}