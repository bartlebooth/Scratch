<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\ContainerAware;

class UserController extends ContainerAware
{
    public function creationForm()
    {
        $templating = $this->container['core::templating']();
        $templating->render(
            __DIR__.'/../Resources/templates/master.html.php', [
                'sectionTitle' => 'Create user',
                'body' => $templating->render(__DIR__.'/../Resources/templates/user_form.html.php', [], false)
            ]
        );
    }

    public function create()
    {
        $validator = new \Scratch\Core\Library\InputValidator($_POST);
        $validator->expect('username')->toBeAlphanumeric(6, 63);
        $validator->expect('password')->toBeConfirmed();
        $validator->expect('password')->toBeAlphanumeric(7, 255);
        $validator->expect('firstName')->toBeString(2, 63);
        $validator->expect('lastName')->toBeString(2, 63);

        if (0 === count($violations = $validator->getViolations())) {
            $input = $validator->getInput();
            $this->container['core::model']('Scratch/Core', 'UserModel')->createUser(
                $input['username'], $input['password'], $input['firstName'], $input['lastName'], 1
            );

            echo 'user created';
        } else {
            $templating = $this->container['core::templating']();
            $templating->render(
                __DIR__.'/../Resources/templates/master.html.php', [
                    'sectionTitle' => 'Create user',
                    'body' => $templating->render(
                        __DIR__.'/../Resources/templates/user_form.html.php',
                        array_merge($_POST, $violations),
                        false
                    )
                ]
            );
        }
    }
}