<?php

namespace Scratch\Core\Library;

/**
 * Interface of renderer objects, i.e. objects used to render a partial view
 * or a fragment of a response.
 */
interface RendererInterface
{
    /**
     * Renders a response fragment.
     *
     * @param array $variables Variables involved in the rendering
     * @return string
     */
    function render(array $variables = []);
}