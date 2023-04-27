<?php
namespace Symfony\Component\HttpFoundation\Session\Storage;
class PhpBridgeSessionStorage extends NativeSessionStorage
{
    public function __construct($handler = null, MetadataBag $metaBag = null)
    {
        $this->setMetadataBag($metaBag);
        $this->setSaveHandler($handler);
    }
    public function start()
    {
        if ($this->started) {
            return true;
        }
        $this->loadSession();
        return true;
    }
    public function clear()
    {
        foreach ($this->bags as $bag) {
            $bag->clear();
        }
        $this->loadSession();
    }
}
