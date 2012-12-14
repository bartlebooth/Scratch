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
        if (!isset($_FILES['avatar'])) {
            if (in_array($_SERVER['REQUEST_METHOD'], array('POST', 'PUT'))) {
                if (empty($_POST) && empty($_FILES)) {
                    // Get maximum size and meassurement unit
                    $max = ini_get('post_max_size');
                    $unit = substr($max, -1);

                    if (!is_numeric($unit)) {
                        $max = substr($max, 0, -1);
                    }

                    // Convert to bytes
                    switch (strtoupper($unit)) {
                        case 'G':
                            $max *= 1024;
                        case 'M':
                            $max *= 1024;
                        case 'K':
                            $max *= 1024;
                    }
                    // Assert the content length is within limits
                    $length = $_SERVER['CONTENT_LENGTH'];

                    if ($max < $length) {
                        echo 'Echec : exceeds ini max post size';
                    }
                }
            }
        } else {
            if ($_FILES['avatar']['error'] === 0) {
                if ($_FILES['avatar']['size'] === 0) {
                    echo 'Pas de fichier téléchargé';
                } else {
                    echo 'Fichier téléchargé avec succès';
                }
            } else {
                echo 'Échec de du téléchargement du fichier : ';

                switch ($_FILES['avatar']['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        echo 'Exceeds max ini size';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        echo 'Exceeds max form size';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        echo 'Partial upload';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        echo 'No upload';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        echo 'No tmp dir';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        echo 'Cannot write';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        echo 'Extension error';
                        break;
                    default:
                        echo 'Unknown error';
                }
            }
        }
        die;

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