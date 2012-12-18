<?php

namespace Scratch\Core\Library;

use \Closure;
use \RuntimeException;

class Templating
{
    private $template;
    private $variables;
    private $var;
    private $path;
    private $asset;
    private $call;
    private $flashes;

    public function __construct(Container $container, $frontScript, $webDir)
    {
        $this->output = true;
        $this->variables = ['locale' => $container['config']['locale']];
        $env = $container['env'];
        $matchUrl = $container['match'];
        $this->var = function ($name, $default = null, $raw = false) use ($env) {
            if (isset($this->variables[$name])) {
                if (is_string($this->variables[$name])) {
                    return $raw ? $this->variables[$name] : htmlspecialchars($this->variables[$name]);
                }

                return $this->variables[$name];
            }

            if (null !== $default) {
                if (is_string($default)) {
                    return $raw ? $default : htmlspecialchars($default);
                }

                return $default;
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
        $this->flashes = function () {
            $flashes = [];

            if (isset($_SESSION['flashes'])) {
                 $flashes = $_SESSION['flashes'];
                 unset($_SESSION['flashes']);
            }

            return $flashes;
        };
    }

    public function render($template, array $variables = [])
    {
        ob_start();
        $this->display($template, $variables);

        return ob_get_clean();
    }

    public function display($template, array $variables = [])
    {
        $render = function ($template, array $variables = []) use (&$render) {
            $var = $this->var;
            $path = $this->path;
            $asset = $this->asset;
            $call = $this->call;
            $flashes = $this->flashes;
            $this->template = $template;
            $this->variables = array_merge($this->variables, $variables);
            call_user_func(Closure::bind(function () use ($template, $render, $var, $path, $asset, $call, $flashes) {
                require $template;
            }, null));
        };

        $render($template, $variables);
    }
}