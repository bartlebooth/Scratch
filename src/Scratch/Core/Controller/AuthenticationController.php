<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Controller;
use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;

class AuthenticationController extends Controller implements ModuleConsumerInterface
{
    private $coreModule;

    public function __construct(CoreModule $coreModule)
    {
        $this->coreModule = $coreModule;
    }

    public function loginForm()
    {
        $templating = $this->coreModule->getTemplating();
        echo $templating->render(
            __DIR__.'/../Resources/templates/master.html.php', [
                'sectionTitle' => 'Login',
                'body' => $templating->render(__DIR__.'/../Resources/templates/login.html.php', [], false)
            ]
        );
    }

    public function login()
    {
        header('cache-control: no-cache');
        $user = $this->coreModule->getModel('Scratch/Core', 'UserModel')
            ->getUserByCredentials($_POST['username'], $_POST['password']);

        if (false === $user) {
            $templating = $this->coreModule->getTemplating();
            echo $templating->render(
                __DIR__.'/../Resources/templates/master.html.php', [
                    'sectionTitle' => 'Login',
                    'body' => $templating->render(
                        __DIR__.'/../Resources/templates/login.html.php',
                        ['loginError' => true]
                    )
                ]
            );
        } else {
            $this->coreModule->useSession();
            $_SESSION['userId'] = $user['id'];
            echo 'LOGGED';
        }
    }

    public function logout()
    {
        session_destroy();
        header("Location: {$_SERVER['SCRIPT_NAME']}");
    }
}