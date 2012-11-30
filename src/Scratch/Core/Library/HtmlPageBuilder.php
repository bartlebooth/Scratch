<?php

namespace Scratch\Core\Library;

class HtmlPageBuilder
{
    private $templating;
    private $masterTemplate;
    private $cssFiles;
    private $jsFiles;
    private $body;

    public function __construct(Templating $templating, $masterTemplate)
    {
        $this->templating = $templating;
        $this->masterTemplate = $masterTemplate;
        $this->cssFiles = array();
        $this->jsFiles = array();
        $this->body = '';
    }

    public function addCss($cssFile)
    {
        $this->cssFiles[] = $cssFile;

        return $this;
    }

    public function addScript($jsFile)
    {
        $this->jsFiles[] = $jsFile;

        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    public function setSubTemplate($template, array $vars = [], array $errors = [])
    {

    }

    public function display()
    {
        $this->templating->render($this->masterTemplate, [
            'css' => $this->cssFiles,
            'scripts' => $this->jsFiles,
            'body' => $this->body
        ]);
    }
}