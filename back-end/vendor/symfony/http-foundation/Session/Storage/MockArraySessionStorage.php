<?php
namespace Symfony\Component\HttpFoundation\Session\Storage;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
class MockArraySessionStorage implements SessionStorageInterface
{
    protected $id = '';
    protected $name;
    protected $started = false;
    protected $closed = false;
    protected $data = [];
    protected $metadataBag;
    protected $bags = [];
    public function __construct(string $name = 'MOCKSESSID', MetadataBag $metaBag = null)
    {
        $this->name = $name;
        $this->setMetadataBag($metaBag);
    }
    public function setSessionData(array $array)
    {
        $this->data = $array;
    }
    public function start()
    {
        if ($this->started) {
            return true;
        }
        if (empty($this->id)) {
            $this->id = $this->generateId();
        }
        $this->loadSession();
        return true;
    }
    public function regenerate($destroy = false, $lifetime = null)
    {
        if (!$this->started) {
            $this->start();
        }
        $this->metadataBag->stampNew($lifetime);
        $this->id = $this->generateId();
        return true;
    }
    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        if ($this->started) {
            throw new \LogicException('Cannot set session ID after the session has started.');
        }
        $this->id = $id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function save()
    {
        if (!$this->started || $this->closed) {
            throw new \RuntimeException('Trying to save a session that was not started yet or was already closed');
        }
        $this->closed = false;
        $this->started = false;
    }
    public function clear()
    {
        foreach ($this->bags as $bag) {
            $bag->clear();
        }
        $this->data = [];
        $this->loadSession();
    }
    public function registerBag(SessionBagInterface $bag)
    {
        $this->bags[$bag->getName()] = $bag;
    }
    public function getBag($name)
    {
        if (!isset($this->bags[$name])) {
            throw new \InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
        }
        if (!$this->started) {
            $this->start();
        }
        return $this->bags[$name];
    }
    public function isStarted()
    {
        return $this->started;
    }
    public function setMetadataBag(MetadataBag $bag = null)
    {
        if (null === $bag) {
            $bag = new MetadataBag();
        }
        $this->metadataBag = $bag;
    }
    public function getMetadataBag()
    {
        return $this->metadataBag;
    }
    protected function generateId()
    {
        return hash('sha256', uniqid('ss_mock_', true));
    }
    protected function loadSession()
    {
        $bags = array_merge($this->bags, [$this->metadataBag]);
        foreach ($bags as $bag) {
            $key = $bag->getStorageKey();
            $this->data[$key] = isset($this->data[$key]) ? $this->data[$key] : [];
            $bag->initialize($this->data[$key]);
        }
        $this->started = true;
        $this->closed = false;
    }
}
