<?php

namespace Scratch\Core\Renderer;

use Scratch\Core\Library\RendererInterface;
use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;

class FooterRenderer implements RendererInterface, ModuleConsumerInterface
{
    private $templating;

    public function  __construct(CoreModule $coreModule)
    {
        $this->templating = $coreModule->getTemplating();
    }

    public function render(array $variables = [])
    {
        return $this->templating->render(__DIR__ . '/../Resources/templates/footer.html.php');
    }
}