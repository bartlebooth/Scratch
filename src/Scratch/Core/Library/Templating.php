<?php

namespace Scratch\Core\Library;

use \Closure;
use \RuntimeException;

class Templating
{
    private $output;
    private $template;
    private $variables;
    private $var;
    private $path;
    private $asset;
    private $call;

    public function __construct(Container $container, $frontScript, $webDir)
    {
        $this->output = true;
        $this->variables = ['locale' => $container['config']['locale']];
        $env = $container['env'];
        $matchUrl = $container['match'];
        $this->var = function ($name, $default = null, $raw = false) use ($env) {
            if (isset($this->variables[$name])) {
                return $raw ? $this->variables[$name] : htmlspecialchars($this->variables[$name]);
            }

            if (null !== $default) {
                return $raw ? $default : htmlspecialchars($default);
            }

            if ($env !== 'prod') {
                throw new RuntimeException(
                    "Template '{$this->template}' cannot be rendered : variable '{$name}' is not defined and no default value is provided."
                );
            }
        };
        $this->path = function ($pathInfo, $method = 'GET') use ($env, $matchUrl, $frontScript) {
            if ($env !== 'prod' && !$matchUrl($pathInfo, $method, false)) {
                throw new RuntimeException(
                    "Template '{$this->template}' cannot be rendered : url '{$pathInfo}' doesn't match any route."
                );
            }

            return $frontScript . $pathInfo;
        };
        $this->asset = function ($assetFile) use ($webDir) {
            return $webDir . $assetFile;
        };
        $this->call = function ($renderer) use ($container) {
            $container[$renderer]();
        };
    }
    public function render($template, array $variables = array(), $output = true)
    {
        $this->output = $output;
        $render = function ($template, array $variables = array()) use (&$render) {
            $var = $this->var;
            $path = $this->path;
            $asset = $this->asset;
            $call = $this->call;
            $this->template = $template;
            $this->variables = array_merge($this->variables, $variables);
            ob_start();
            call_user_func(Closure::bind(function () use ($template, $render, $var, $path, $asset, $call) {
                require $template;
            }, null));

            return $this->output ? ob_end_flush() : ob_get_clean();
        };

        return $render($template, $variables);
    }
}