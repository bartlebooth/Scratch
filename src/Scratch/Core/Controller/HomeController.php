<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Controller;

class HomeController extends Controller
{
    public function index()
    {
        /*
        $user = $this->container['core::model']('Scratch/Core', 'UserModel')->getUserByCredentials('admin', 'admin');
        $user = $this->container['core::model']('Scratch/Core', 'UserModel')->getUserById(1);
        $user = $this->container['core::security']()->getUser();

        http_response_code(200);
        $this->container['core::templating']()->display(
            __DIR__.'/../Resources/templates/master.html.php', [
                'sectionTitle' => 'Accueil',
                'body' => print_r($user, true)
            ]
        );*/

        http_response_code(200);
        echo '<h1>Accueil (tmp)</h1';
    }
}