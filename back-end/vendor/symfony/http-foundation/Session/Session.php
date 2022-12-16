<?php
namespace Symfony\Component\HttpFoundation\Session;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
class Session implements SessionInterface, \IteratorAggregate, \Countable
{
    protected $storage;
    private $flashName;
    private $attributeName;
    private $data = [];
    private $usageIndex = 0;
    public function __construct(SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null)
    {
        $this->storage = $storage ?: new NativeSessionStorage();
        $attributes = $attributes ?: new AttributeBag();
        $this->attributeName = $attributes->getName();
        $this->registerBag($attributes);
        $flashes = $flashes ?: new FlashBag();
        $this->flashName = $flashes->getName();
        $this->registerBag($flashes);
    }
    public function start()
    {
        return $this->storage->start();
    }
    public function has($name)
    {
        return $this->getAttributeBag()->has($name);
    }
    public function get($name, $default = null)
    {
        return $this->getAttributeBag()->get($name, $default);
    }
    public function set($name, $value)
    {
        $this->getAttributeBag()->set($name, $value);
    }
    public function all()
    {
        return $this->getAttributeBag()->all();
    }
    public function replace(array $attributes)
    {
        $this->getAttributeBag()->replace($attributes);
    }
    public function remove($name)
    {
        return $this->getAttributeBag()->remove($name);
    }
    public function clear()
    {
        $this->getAttributeBag()->clear();
    }
    public function isStarted()
    {
        return $this->storage->isStarted();
    }
    public function getIterator()
    {
        return new \ArrayIterator($this->getAttributeBag()->all());
    }
    public function count()
    {
        return \count($this->getAttributeBag()->all());
    }
    public function getUsageIndex()
    {
        return $this->usageIndex;
    }
    public function isEmpty()
    {
        if ($this->isStarted()) {
            ++$this->usageIndex;
        }
        foreach ($this->data as &$data) {
            if (!empty($data)) {
                return false;
            }
        }
        return true;
    }
    public function invalidate($lifetime = null)
    {
        $this->storage->clear();
        return $this->migrate(true, $lifetime);
    }
    public function migrate($destroy = false, $lifetime = null)
    {
        return $this->storage->regenerate($destroy, $lifetime);
    }
    public function save()
    {
        $this->storage->save();
    }
    public function getId()
    {
        return $this->storage->getId();
    }
    public function setId($id)
    {
        if ($this->storage->getId() !== $id) {
            $this->storage->setId($id);
        }
    }
    public function getName()
    {
        return $this->storage->getName();
    }
    public function setName($name)
    {
        $this->storage->setName($name);
    }
    public function getMetadataBag()
    {
        ++$this->usageIndex;
        return $this->storage->getMetadataBag();
    }
    public function registerBag(SessionBagInterface $bag)
    {
        $this->storage->registerBag(new SessionBagProxy($bag, $this->data, $this->usageIndex));
    }
    public function getBag($name)
    {
        return $this->storage->getBag($name)->getBag();
    }
    public function getFlashBag()
    {
        return $this->getBag($this->flashName);
    }
    private function getAttributeBag()
    {
        return $this->getBag($this->attributeName);
    }
}
