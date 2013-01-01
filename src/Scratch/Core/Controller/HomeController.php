<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Controller;
use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;

class HomeController extends Controller implements ModuleConsumerInterface
{
    private $coreModule;

    public function __construct(CoreModule $coreModule)
    {
        $this->coreModule = $coreModule;
    }

    public function index()
    {
        $user = $this->coreModule->getModel('Scratch/Core', 'UserModel')->getUserByCredentials('admin', 'admin');
        $user = $this->coreModule->getModel('Scratch/Core', 'UserModel')->getUserById(1);
        $user = $this->coreModule->getSecurity()->getUser();

        http_response_code(200);
        echo $this->coreModule->getTemplating()->render(
            __DIR__.'/../Resources/templates/master.html.php', [
                'sectionTitle' => 'Accueil',
                'body' => print_r($user, true)
            ]);
    }
}