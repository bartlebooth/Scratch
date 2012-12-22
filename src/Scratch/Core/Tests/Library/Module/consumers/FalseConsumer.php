<?php

/*
 * This class does not implement ModuleConsumerInterface. When its FQCN is
 * passed to the ModuleManager::createConsumer() method, a new instance
 * should be returned without trying to inject any module.
 */
class FalseConsumer
{
}