<?php

namespace Scratch\Core\Controller;

use Scratch\Core\Library\Controller;
use Scratch\Core\Library\Module\ModuleConsumerInterface;
use Scratch\Core\Module\CoreModule;
use Scratch\Core\Library\PostLimitException;
use Scratch\Core\Library\Validation\ValidationException;

class UserController extends Controller implements ModuleConsumerInterface
{
    private $coreModule;

    public function __construct(CoreModule $coreModule)
    {
        $this->coreModule = $coreModule;
    }

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
        try {
            header('cache-control: no-cache');
            $data = $this->getPostedData();
            $this->coreModule->getModel('Scratch/Core', 'UserModel')->createUser($data);
            $this->coreModule->useSession();
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
//        $this->container['core::masterPage']()
//            ->setSectionTitle('Create user')
//            ->setBody(__DIR__.'/../Resources/templates/user_form.html.php', $data)
//            ->display();
        $templating = $this->coreModule->getTemplating();
        echo $templating->render(
            __DIR__.'/../Resources/templates/master.html.php', [
                'sectionTitle' => 'Create user',
                'body' => $templating->render(
                    __DIR__.'/../Resources/templates/user_form.html.php',
                    $data
                )
            ]
        );
    }
}