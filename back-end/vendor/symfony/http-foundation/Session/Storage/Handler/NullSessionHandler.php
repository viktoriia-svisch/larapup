<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class NullSessionHandler extends AbstractSessionHandler
{
    public function close()
    {
        return true;
    }
    public function validateId($sessionId)
    {
        return true;
    }
    protected function doRead($sessionId)
    {
        return '';
    }
    public function updateTimestamp($sessionId, $data)
    {
        return true;
    }
    protected function doWrite($sessionId, $data)
    {
        return true;
    }
    protected function doDestroy($sessionId)
    {
        return true;
    }
    public function gc($maxlifetime)
    {
        return true;
    }
}
