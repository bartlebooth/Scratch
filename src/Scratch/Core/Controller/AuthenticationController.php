<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\ContainerAware;

class AuthenticationController extends ContainerAware
{
    public function loginForm()
    {
        $templating = $this->container['core::templating']();
        $templating->render(
            __DIR__.'/../Resources/templates/master.html.php', [
                'sectionTitle' => 'Login',
                'body' => $templating->render(__DIR__.'/../Resources/templates/login.html.php', [], false)
            ]
        );
    }

    public function login()
    {
        header('cache-control: no-cache');
        $user = $this->container['core::model']('Scratch/Core', 'UserModel')
            ->getUserByCredentials($_POST['username'], $_POST['password']);

        if (!$user) {
            $templating = $this->container['core::templating']();
            $templating->render(
                __DIR__.'/../Resources/templates/master.html.php', [
                    'sectionTitle' => 'Login',
                    'body' => $templating->render(
                        __DIR__.'/../Resources/templates/login.html.php',
                        ['loginError' => true],
                        false
                    )
                ]
            );
        } else {
            $_SESSION['userId'] = $user->id;
            echo 'LOGGED';
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: {$_SERVER['SCRIPT_NAME']}");
    }
}