<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Controller;
use Scratch\Core\Library\ValidationException;

class UserController extends Controller
{
    public function creationForm()
    {
        $this->container['core::masterPage']()
            ->setSectionTitle('Create user')
            ->setBody(__DIR__.'/../Resources/templates/user_form.html.php')
            ->display();
    }

    public function create()
    {
        header('cache-control: no-cache');
        $data = $this->filter($_POST);

        try {
            $this->container['core::model']('Scratch/Core', 'UserModel')->createUser($data);
            $_SESSION['flashes']['success'][] = 'User created';
            $this->creationForm();
        } catch (ValidationException $ex) {
            $this->container['core::masterPage']()
                ->setSectionTitle('Create user')
                ->setBody(__DIR__.'/../Resources/templates/user_form.html.php', array_merge($data, $ex->getViolations()))
                ->display();
        }
    }
}