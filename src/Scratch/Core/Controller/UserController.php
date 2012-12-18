<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Controller;
use Scratch\Core\Library\PostLimitException;
use Scratch\Core\Library\ValidationException;

class UserController extends Controller
{
    public function testForm(array $data = [])
    {
        $collections = [
            'select::items' => [
                '1' => 'Option 1',
                '2' => 'Option 2',
                '3' => 'Option 3'
            ],
            'selectMultiple::items' => [
                '1' => 'Option 4',
                '2' => 'Option 5',
                '3' => 'Option 6'
            ],
            'uncheckedRadio::items' => [
                '1' => 'Option 7',
                '2' => 'Option 8',
                '3' => 'Option 9',
            ],
            'checkedRadio::items' => [
                '1' => 'Option A',
                '2' => 'Option B',
                '3' => 'Option C',
            ],
            'uncheckedBoxes::items' => [
                '1' => 'Option D',
                '2' => 'Option E',
                '3' => 'Option F',
            ],
            'checkedBoxes::items' => [
                '1' => 'Option G',
                '2' => 'Option H',
                '3' => 'Option I',
            ]
        ];
        $defaults = [
            'selectMultiple' => ['1', '2'],
            'checkedRadio' => '1',
            'checkedBoxes' => ['1', '2']
        ];
        $this->container['core::templating']()->display(
            __DIR__.'/../Resources/templates/test_form.html.php',
            count($data) > 0 ?
                array_merge($collections, $data) :
                array_merge($collections, $defaults)
        );
    }

    public function test()
    {
        //var_dump($_POST);
        //var_dump($_FILES);

        $validator = $this->container['core::validator']();
        $validator->setProperties($_POST);

        $validator->expect('text');
        $validator->expect('password');
        $validator->expect('select');
        $validator->expect('selectMultiple');
        $validator->expect('checkedRadio');
        $validator->expect('uncheckedRadio');
        $validator->expect('checkedBoxes');
        $validator->expect('uncheckedBoxes');

        $this->testForm(array_merge($_POST, $validator->getViolations()));
    }

    public function creationForm()
    {
        $this->displayForm();
    }

    public function create()
    {
        /*
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
        */

        try {
            header('cache-control: no-cache');
            //$this->throwExceptionOnRequestError();
            $data = $this->getPostedData();
            var_dump($data); die;
            $this->container['core::model']('Scratch/Core', 'UserModel')->createUser($data);
            $_SESSION['flashes']['success'][] = 'User created';
            $this->displayForm();
        } catch (PostLimitException $ex) {
            $this->displayForm(['avatar::errors' => ['File is too large']]);
        } catch (ValidationException $ex) {
            $this->displayForm(array_merge($data, $ex->getViolations()));
        }
    }

    private function displayForm(array $data = [])
    {
        $this->container['core::masterPage']()
            ->setSectionTitle('Create user')
            ->setBody(__DIR__.'/../Resources/templates/user_form.html.php', $data)
            ->display();
    }
}