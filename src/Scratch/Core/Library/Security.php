<?php

namespace Scratch\Core\Library;

use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;

class Security implements ModuleConsumerInterface
{
    private $coreModule;

    public function __construct(CoreModule $coreModule)
    {
        $this->coreModule = $coreModule;
    }

    public function getUser()
    {
        $this->coreModule->useSession();

        if (isset($_SESSION['userId'])) {
            return $this->coreModule->getModel('Scratch/Core', 'UserModel')->getUserById($_SESSION['userId']);
        }

        return 'ANONYMOUS';
    }
}