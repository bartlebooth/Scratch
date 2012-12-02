<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\ContainerAware;
use Scratch\Core\Library\ValidationException;

class UserController extends ContainerAware
{
    public function creationForm()
    {
        $this->container['core::masterPage']()
            ->setSectionTitle('Create user')
            ->setBody(__DIR__.'/../Resources/templates/user_form.html.php')
            ->display();
    }

    private function trim(array $properties)
    {
        foreach ($properties as $property => $value) {
            if (is_string($value)) {
                $properties[$property] = trim($value);
            } elseif (is_array($value)) {
                $properties[$property] = $this->trim($value);
            }
        }

        return $properties;
    }

    public function create()
    {
        header('cache-control: no-cache');
        $data = $this->trim($_POST);

        try {
            $this->container['core::model']('Scratch/Core', 'UserModel')->createUser($data);
            echo 'user created';
        } catch (ValidationException $ex) {
            $this->container['core::masterPage']()
                ->setSectionTitle('Create user')
                ->setBody(__DIR__.'/../Resources/templates/user_form.html.php', array_merge($data, $ex->getViolations()))
                ->display();
        }
    }
}