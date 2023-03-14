<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;
class MigratingSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface
{
    private $currentHandler;
    private $writeOnlyHandler;
    public function __construct(\SessionHandlerInterface $currentHandler, \SessionHandlerInterface $writeOnlyHandler)
    {
        if (!$currentHandler instanceof \SessionUpdateTimestampHandlerInterface) {
            $currentHandler = new StrictSessionHandler($currentHandler);
        }
        if (!$writeOnlyHandler instanceof \SessionUpdateTimestampHandlerInterface) {
            $writeOnlyHandler = new StrictSessionHandler($writeOnlyHandler);
        }
        $this->currentHandler = $currentHandler;
        $this->writeOnlyHandler = $writeOnlyHandler;
    }
    public function close()
    {
        $result = $this->currentHandler->close();
        $this->writeOnlyHandler->close();
        return $result;
    }
    public function destroy($sessionId)
    {
        $result = $this->currentHandler->destroy($sessionId);
        $this->writeOnlyHandler->destroy($sessionId);
        return $result;
    }
    public function gc($maxlifetime)
    {
        $result = $this->currentHandler->gc($maxlifetime);
        $this->writeOnlyHandler->gc($maxlifetime);
        return $result;
    }
    public function open($savePath, $sessionName)
    {
        $result = $this->currentHandler->open($savePath, $sessionName);
        $this->writeOnlyHandler->open($savePath, $sessionName);
        return $result;
    }
    public function read($sessionId)
    {
        return $this->currentHandler->read($sessionId);
    }
    public function write($sessionId, $sessionData)
    {
        $result = $this->currentHandler->write($sessionId, $sessionData);
        $this->writeOnlyHandler->write($sessionId, $sessionData);
        return $result;
    }
    public function validateId($sessionId)
    {
        return $this->currentHandler->validateId($sessionId);
    }
    public function updateTimestamp($sessionId, $sessionData)
    {
        $result = $this->currentHandler->updateTimestamp($sessionId, $sessionData);
        $this->writeOnlyHandler->updateTimestamp($sessionId, $sessionData);
        return $result;
    }
}
