<?php

namespace Scratch\Core\Library\Module;

interface ModuleInterface
{
    function setApplicationParameters(array $definitions, array $config, $environment);
}