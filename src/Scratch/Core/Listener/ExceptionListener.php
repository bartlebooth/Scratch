<?php

namespace Scratch\Core\Listener;

use \Exception;
use Scratch\Core\Library\ContainerAware;

class ExceptionListener extends ContainerAware
{
    public function onException(Exception $ex)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // unset all previous headers !!!

        switch ($ex->getCode()) {
            case '403':
                $code = 403;
                $text = 'Forbidden';
                break;
            case '404':
                $code = 404;
                $text = 'Not found';
                break;
            default:
                $code = 500;
                $text = 'Internal server error';
        }

        http_response_code($code);

        if ($this->container['env'] !== 'prod') {
            throw $ex;
        }

        echo "<h1>Http error :</h1>
              <h3>{$code} {$text}</h3>
              <h4>(returned by Scratch)</h4>";
    }
}