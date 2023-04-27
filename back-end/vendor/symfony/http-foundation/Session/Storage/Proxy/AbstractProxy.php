<?php
namespace Symfony\Component\HttpFoundation\Session\Storage\Proxy;
abstract class AbstractProxy
{
    protected $wrapper = false;
    protected $saveHandlerName;
    public function getSaveHandlerName()
    {
        return $this->saveHandlerName;
    }
    public function isSessionHandlerInterface()
    {
        return $this instanceof \SessionHandlerInterface;
    }
    public function isWrapper()
    {
        return $this->wrapper;
    }
    public function isActive()
    {
        return \PHP_SESSION_ACTIVE === session_status();
    }
    public function getId()
    {
        return session_id();
    }
    public function setId($id)
    {
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the ID of an active session');
        }
        session_id($id);
    }
    public function getName()
    {
        return session_name();
    }
    public function setName($name)
    {
        if ($this->isActive()) {
            throw new \LogicException('Cannot change the name of an active session');
        }
        session_name($name);
    }
}
