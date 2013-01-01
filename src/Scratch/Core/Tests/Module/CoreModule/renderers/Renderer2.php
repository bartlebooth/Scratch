<?php

use Scratch\Core\Library\RendererInterface;
use Scratch\Core\Library\Module\ModuleConsumerInterface;

class Renderer2 implements RendererInterface, ModuleConsumerInterface
{
    private $module1;

    public function __construct(ValidModule1 $module1)
    {
        $this->module1 = $module1;
    }

    public function render(array $variables = [])
    {
        return 'Renderer2 output';
    }

    public function getModule1()
    {
        return $this->module1;
    }
}