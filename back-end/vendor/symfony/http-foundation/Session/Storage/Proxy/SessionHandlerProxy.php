<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Proxy;
class SessionHandlerProxy extends AbstractProxy implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
    protected $handler;
    public function __construct(\SessionHandlerInterface $handler)
    {
        $this->handler = $handler;
        $this->wrapper = ($handler instanceof \SessionHandler);
        $this->saveHandlerName = $this->wrapper ? ini_get('session.save_handler') : 'user';
    }
    public function getHandler()
    {
        return $this->handler;
    }
    public function open($savePath, $sessionName)
    {
        return (bool) $this->handler->open($savePath, $sessionName);
    }
    public function close()
    {
        return (bool) $this->handler->close();
    }
    public function read($sessionId)
    {
        return (string) $this->handler->read($sessionId);
    }
    public function write($sessionId, $data)
    {
        return (bool) $this->handler->write($sessionId, $data);
    }
    public function destroy($sessionId)
    {
        return (bool) $this->handler->destroy($sessionId);
    }
    public function gc($maxlifetime)
    {
        return (bool) $this->handler->gc($maxlifetime);
    }
    public function validateId($sessionId)
    {
        return !$this->handler instanceof \SessionUpdateTimestampHandlerInterface || $this->handler->validateId($sessionId);
    }
    public function updateTimestamp($sessionId, $data)
    {
        return $this->handler instanceof \SessionUpdateTimestampHandlerInterface ? $this->handler->updateTimestamp($sessionId, $data) : $this->write($sessionId, $data);
    }
}
