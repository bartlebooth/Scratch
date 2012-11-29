<?php

namespace Scratch\Core\Library;

class SessionHandler implements \SessionHandlerInterface
{
    private $sessionDir;
    private $maxLifetime;

    public function __construct($sessionDir, $maxLifetime)
    {
        $this->sessionDir = $sessionDir;
        $this->maxLifetime = $maxLifetime;
    }

    public function open($savePath, $sessionName)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        $file = "{$this->sessionDir}/{$sessionId}";

        if (!file_exists($file) || ($this->maxLifetime > 0 && fileatime($file) + $this->maxLifetime < time())) {
            file_put_contents($file, '');

            return '';
        }

        return file_get_contents($file);
     }

     public function write($sessionId, $sessionData)
     {
        return file_put_contents("{$this->sessionDir}/{$sessionId}", $sessionData) > 0;
     }

     public function destroy($sessionId)
     {
        return unlink("{$this->sessionDir}/{$sessionId}");
     }

    public function gc($maxLifetime)
    {
        if ($this->maxLifetime > 0) {
            foreach (new \DirectoryIterator($this->sessionDir) as $item) {
                if ($item->isFile() && $item->getATime() + $this->maxLifetime < time()) {
                    unlink($item->getPathname());
                }
            }
        }

        return true;
    }
}