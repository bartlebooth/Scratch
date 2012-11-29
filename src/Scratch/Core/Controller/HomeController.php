<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\ContainerAware;

class HomeController extends ContainerAware
{
    public function index()
    {
//        $validator = new \Scratch\Core\Library\ArrayValidator();
//        $validator->setRules([
//            'foo' => ['required', 'regex', '#^\d+$#', 'mustBeADigit'],
//            'bar' => ['required', 'collection', [
//
//                ]
//            ]
//        ]);
//        $errors = $validator->validate([
//            'foo' => '123'
//        ]);
//
//        var_dump($errors);

        $user = $this->container['core::model']('Scratch/Core', 'UserModel')->getUserByCredentials('admin', 'admin');
        $user = $this->container['core::model']('Scratch/Core', 'UserModel')->getUserById(1);
        $user = $this->container['core::security']()->getUser();

        http_response_code(200);
        $this->container['core::templating']()->render(
            __DIR__.'/../Resources/templates/master.html.php', [
                'sectionTitle' => 'Accueil',
                'body' => print_r($user, true)
            ]
        );
    }
}