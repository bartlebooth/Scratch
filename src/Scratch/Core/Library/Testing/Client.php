<?php

namespace Scratch\Core\Library\Testing;

use Scratch\Core\Library\Module\ModuleManager;

class Client
{
    /** @var ModuleManager */
    private $moduleManager;

    public function __construct(array $config = null)
    {
        $config = $config ? $config : require __DIR__ . '/../../../../../config/main.php';
        $routing = [];
        $modules = [];
        $listeners = [];
        $translations = [];

        foreach ($config['packages'] as $package => $isActive) {
            $isActive && require "{$config['srcDir']}/{$package}/Resources/config/definitions.php";
        }

        $this->moduleManager = new ModuleManager(
            [
                'routing' => $routing,
                'modules' => $modules,
                'listeners' => $listeners,
                'translations' => $translations
            ],
            $config,
            [],
            'test'
        );
    }

    public function request($pathInfo, $method)
    {
        ob_start();
        $this->moduleManager->getModule('Scratch\Core\Module\CoreModule')->matchUrl($pathInfo, $method);

        return [
            'headers' => xdebug_get_headers(),
            'code' => http_response_code(),
            'body' => ob_get_clean()
        ];
    }

    public function getModule($moduleFqcn)
    {
        return $this->moduleManager->getModule($moduleFqcn);
    }
}