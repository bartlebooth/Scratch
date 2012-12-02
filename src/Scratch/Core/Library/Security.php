<?php

namespace Scratch\Core\Library;

use Scratch\Core\Model\Api\AbstractUserModel;

class Security
{
    private $userModel;

    public function __construct(AbstractUserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function getUser()
    {
        if (isset($_SESSION['userId'])) {
            return $this->userModel->getUserById($_SESSION['userId']);
        }

        return 'ANONYMOUS';
    }
}