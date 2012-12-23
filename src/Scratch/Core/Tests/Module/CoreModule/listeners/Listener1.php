<?php

class Listener1
{
    private $hasBeenCalled = false;
    private $generatedNumber;

    public function onFoo(stdClass $event)
    {
        $this->hasBeenCalled = true;
        $this->generateNumber();
        $event->listenerReferences[] = $this;
    }

    public function onBar(stdClass $event)
    {
        $this->hasBeenCalled = true;
        $this->generateNumber();
        $event->listenerReferences[] = $this;
    }

    /**
     * Generates a (quite) unique number per instance, used to ensure
     * the same instance is used for multiple calls.
     */
    private function generateNumber()
    {
        if (!isset($this->generatedNumber)) {
            $this->generatedNumber = rand(0, 1000000) + microtime();
        }
    }
}
