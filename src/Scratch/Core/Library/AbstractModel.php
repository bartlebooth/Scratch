<?php

namespace Scratch\Core\Library;

use \PDO;

abstract class AbstractModel
{
    /** @var \PDO */
    protected $connection;
    /** @var ArrayValidator */
    protected $validator;

    public function setConnection(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function setValidator(ArrayValidator $validator)
    {
        $this->validator = $validator;
    }
}