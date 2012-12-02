<?php

namespace Scratch\Core\Library;

class HtmlPageBuilder
{
    private $templating;
    private $masterTemplate;
    private $sectionTitle;
    private $body;

    public function __construct(Templating $templating, $masterTemplate)
    {
        $this->templating = $templating;
        $this->masterTemplate = $masterTemplate;
        $this->sectionTitle = '';
        $this->body = '';
    }

    public function setSectionTitle($title)
    {
        $this->sectionTitle = $title;

        return $this;
    }

    public function setBody($template, array $variables = [])
    {
        $this->body = $this->templating->render($template, $variables, false);

        return $this;
    }

    public function display()
    {
        $this->templating->render($this->masterTemplate, [
            'sectionTitle' => $this->sectionTitle,
            'body' => $this->body
        ]);
    }
}