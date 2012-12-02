<?php

namespace Scratch\Core\Library;

class Client
{
    private $container;

    public function __construct(array $config = null)
    {
        $config = $config ? $config : require __DIR__ . '/../../../../config/main.php';
        $routing = array();
        $modules = array();
        $listeners = array();

        foreach ($config['packages'] as $package => $isActive) {
            $isActive && require "{$config['srcDir']}/{$package}/Resources/config/definitions.php";
        }

        $this->container = new Container('test', $config, $routing, $modules, $listeners);
    }

    public function request($pathInfo, $method)
    {
        ob_start();
        $this->container['match']($pathInfo, $method);

        return [
            'headers' => xdebug_get_headers(),
            'code' => http_response_code(),
            'body' => ob_get_clean()
        ];
    }

    public function getContainer()
    {
        return $this->container;
    }
}