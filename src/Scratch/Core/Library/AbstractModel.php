<?php

namespace Scratch\Core\Library;

abstract class AbstractModel
{
    protected $connection;
    protected $validator;

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }

    public function setValidator(ArrayValidator $validator)
    {
        $this->validator = $validator;
    }
}