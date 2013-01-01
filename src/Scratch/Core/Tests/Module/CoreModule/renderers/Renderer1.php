<?php

use Scratch\Core\Library\RendererInterface;

class Renderer1 implements RendererInterface
{
    public function render(array $variables = [])
    {
        return 'Renderer1 output';
    }
}