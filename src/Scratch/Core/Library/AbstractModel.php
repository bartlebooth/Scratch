<?php

namespace Scratch\Core\Library;

abstract class AbstractModel
{
    protected $connection;

    public function setConnection($connection)
    {
        $this->connection = $connection;
    }
}