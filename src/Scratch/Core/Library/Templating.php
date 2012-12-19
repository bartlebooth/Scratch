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
    private $formRow;

    public function __construct(Container $container, $frontScript, $webDir)
    {
        $this->variables = ['locale' => $container['config']['locale']];
        $env = $container['env'];
        $matchUrl = $container['match'];
        $this->var = $var = function ($name, $default = null, $raw = false) use ($env) {
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
        // conventions (::errors, ::items); options (size[file, textarea], disabled, arrayField)
        $this->formRow = function ($type, $fieldName, $label, $options = []) use ($var) {
            $realFieldName = isset($options['arrayField']) && $options['arrayField'] ? "{$fieldName}[]" : $fieldName;
            $disabled = isset($options['disabled']) && $options['disabled'] ? 'disabled="disabled"' : '';
            $rowMask = '<div class="control-group">%s%s</div>';
            $labelMask = '<label class="control-label" for="%s">%s :</label>';
            $controlsMask = '<div class="controls">%s<span class="help-inline"><ul>%s</ul></span></div>';
            $inputMask = '<input type="%s" name="%s" value="%s" %s/>';
            $textAreaMask = '<textarea name="%s" %s>%s</textarea>';
            $selectMask = '<select name="%s" %s>%s</select>';
            $optionMask = '<option value="%s" %s>%s</option>';
            $controls = '';
            $errors = '';

            switch ($type) {
                case 'text':
                    $controls .= sprintf($inputMask, 'text', $realFieldName, $var($fieldName, ''), $disabled);
                    break;
                case 'password':
                    $controls .= sprintf($inputMask, 'password', $realFieldName, '', $disabled);
                    break;
                case 'textarea':
                    $size = isset($options['size']) ? "maxlength=\"{$options['size']}\" " : ' ';
                    $controls .= sprintf($textAreaMask, $fieldName, $size . $disabled, $var($fieldName, ''));
                    break;
                case 'select':
                case 'selectMultiple':
                    $items = '';

                    foreach ($var("{$fieldName}::items") as $id => $item) {
                        $isSelected = $type === 'select' ?
                            $var($fieldName, false) && ($var($fieldName) == $id) :
                            is_array($var($fieldName, false)) && in_array($id, $var($fieldName));
                        $items .= sprintf($optionMask, $id, $isSelected ? 'selected="selected"' : '', $item);
                    }

                    list($name, $multiple) = $type === 'select' ? [$fieldName, ''] : ["{$fieldName}[]", ' multiple="multiple"'];
                    $controls .= sprintf($selectMask, $name, $disabled . $multiple, $items);
                    break;
                case 'radio':
                case 'checkbox':
                    foreach ($var("{$fieldName}::items") as $id => $item) {
                        $isChecked = $type === 'radio' ?
                            $var($fieldName, false) && ($var($fieldName) == $id) :
                            is_array($var($fieldName, false)) && in_array($id, $var($fieldName));
                        $name = $type === 'checkbox' ? "{$fieldName}[]" : $fieldName;
                        $checked = $isChecked ? ' checked="checked"' : '';
                        $controls .= sprintf($inputMask, $type, $name, $id, $disabled . $checked) . $item;
                    }

                    break;
                case 'file':
                    isset($options['size']) && $controls .= sprintf($inputMask, 'hidden', 'MAX_FILE_SIZE', $options['size'], '');
                    $controls .= sprintf($inputMask, 'file', $fieldName, '', $disabled);
                    break;
            }

            foreach ($var("{$fieldName}::errors", []) as $error) {
                $errors .= "<li>{$error}</li>";
            }

            return sprintf(
                $rowMask,
                sprintf($labelMask, $fieldName, $label),
                sprintf($controlsMask, $controls, $errors)
            );
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
            $formRow = $this->formRow;
            $this->template = $template;
            $this->variables = array_merge($this->variables, $variables);
            call_user_func(Closure::bind(function () use ($template, $render, $var, $path, $asset, $call, $flashes, $formRow) {
                require $template;
            }, null));
        };

        $render($template, $variables);
    }
}