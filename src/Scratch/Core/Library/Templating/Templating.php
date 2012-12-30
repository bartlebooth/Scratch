<?php

namespace Scratch\Core\Library\Templating;

use \Closure;
use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;
use Scratch\Core\Library\Templating\Exception\UndefinedVariableException;
use Scratch\Core\Module\Exception\NotFoundException;
use Scratch\Core\Library\Templating\Exception\UnknownControlTypeException;

/**
 * Class providing basic templating functionalities.
 */
class Templating implements ModuleConsumerInterface
{
    /**
     * Instance of the core module.
     *
     * @var Scratch\Core\Module\CoreModule $coreModule
     */
    private $coreModule;

    /**
     * Configuration of the application.
     *
     * @var array
     */
    private $configuration;

    /**
     * Environment of the application.
     *
     * @var string
     */
    private $environment;

    /**
     * Web path of the front controller script.
     *
     * @var string
     */
    private $frontScript;

    /**
     * Web path of the public directory.
     *
     * @var string
     */
    private $publicWebPath;

    /**
     * Collection of templating helpers.
     *
     * @var array[Closure]
     */
    private $helpers;

    /**
     * Variables of the current template.
     *
     * @var array
     */
    private $variables;

    /**
     * Pathname of the current template.
     *
     * @var string
     */
    private $template;

    /**
     * Constructor.
     *
     * @param Scratch\Core\Module\CoreModule $coreModule
     */
    public function __construct(CoreModule $coreModule)
    {
        $this->coreModule = $coreModule;
        $this->configuration = $coreModule->getConfiguration();
        $this->environment = $coreModule->getEnvironment();
        $this->frontScript = $coreModule->getContext()['frontScript'];
        $this->publicWebPath = preg_replace('#/[^/]*$#', '', $this->frontScript);
        $this->helpers = $this->getHelpers();
        $this->variables = [];

        // add 'raw' helper
        // add 'config' helper
        // add 'trans' helper
        // add 'generic' helper

//        $this->call = function ($renderer) use ($container) {
//            $container[$renderer]();
//        };
//        $this->flashes = function () {
//            $flashes = [];
//
//            if (isset($_SESSION['flashes'])) {
//                 $flashes = $_SESSION['flashes'];
//                 unset($_SESSION['flashes']);
//            }
//
//            return $flashes;
//        };
    }

    /**
     * Displays a template, rendering it and letting it echoing to std output.
     *
     * @param string    $template   Pathname of the template to display
     * @param array     $variables  Variables to be passed to the template
     */
    public function display($template, array $variables = [])
    {
        $render = function ($template, array $variables = []) use (&$render) {
            $this->template = $template;
            $this->variables = array_merge($this->variables, $variables);
            $var = $this->helpers['var'];
            $path = $this->helpers['path'];
            $asset = $this->helpers['asset'];
            $formRow = $this->helpers['formRow'];
            call_user_func(Closure::bind(function () use ($template, $render, $var, $path, $asset, $formRow) {
                require $template;
            }, null));
        };

        $render($template, $variables);
    }

    /**
     * Renders a template, returning the string output.
     *
     * @param string    $template   Pathname of the template to display
     * @param array     $variables  Variables to be passed to the template
     * @return string
     */
    public function render($template, array $variables = [])
    {
        ob_start();
        $this->display($template, $variables);

        return ob_get_clean();
    }

    /**
     * Returns the base templating helpers (testing purpose).
     *
     * @return array[Closure]
     */
    public function getHelpers()
    {
        return [
            'var' => $this->getVarHelper(),
            'path' => function ($pathInfo, $method = 'GET') {
                if ($this->environment !== 'prod' && !$this->coreModule->matchUrl($pathInfo, $method, false)) {
                    throw new NotFoundException(
                        "Template '{$this->template}' cannot be rendered : url '{$pathInfo}' doesn't match any route."
                    );
                }

                return $this->frontScript . $pathInfo;
            },
            'asset' => function ($assetFile) {
                return $this->publicWebPath . $assetFile;
            },
            'formRow' => $this->getFormRowHelper()
        ];
    }

    /**
     * Sets the template variables (testing purpose).
     *
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    /**
     * Returns the var helper.
     *
     * This helper is used to access the value of the variables passed to the template.
     * Its parameters are :
     * - name : name of the variable to access
     * - default (optional) : default value to use if the variable is not defined (if set,
     *                        the default value cannot be null)
     *
     * If the value is a string, it will be escaped. In dev and test environment, if the variable
     * is not defined and no default value is provided, an exception will be thrown.
     *
     * @return Closure
     */
    private function getVarHelper()
    {
        return function ($name, $default = null) {
            if (isset($this->variables[$name])) {
                return is_string($this->variables[$name]) ?
                    htmlspecialchars($this->variables[$name], ENT_QUOTES, 'UTF-8') :
                    $this->variables[$name];
            }

            if (null !== $default) {
                return $default;
            }

            if ($this->environment !== 'prod') {
                throw new UndefinedVariableException(
                    "Template '{$this->template}' cannot be rendered : variable '{$name}' is not defined and no default value was provided."
                );
            }
        };
    }

    /**
     * Returns the form row helper.
     *
     * This helper has the following parameters :
     * - type : the type of form control (text|password|textarea|select|selectMultiple|radio|checkbox)
     * - fieldName : the name of the field
     * - label : the label of the field
     * - options (optional) : an array containing one or more of the following options :
     *     - size => integer : accepted size of the input (for textarea and file control types).
     *     - disabled => boolean : if set to true, the input is disabled.
     *     - arrayField => boolean : if set to true, the control name will be written as an array,
     *                               e.g. "name[]" (useful for repeated fields).
     *
     * Errors related to the row can be displayed if a variable named with the original field name
     * suffixed by "::errors" is passed to the template.
     *
     * Composite control types (i.e. select, selectMultiple, radio and checkbox) need their options
     * to be passed to the template using a variable named with the original field named suffixed
     * by "::items". This variable must be an associative array ("id of the option" => "value").
     *
     * @return Closure
     */
    private function getFormRowHelper()
    {
        $var = $this->getVarHelper();

        return function ($type, $fieldName, $label, $options = []) use ($var) {
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
                    $size = isset($options['size']) ? "maxlength=\"{$options['size']}\" " : '';
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
                default:
                    throw new UnknownControlTypeException("Unknown form control type '{$type}'");
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
}