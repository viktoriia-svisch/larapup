<?php
namespace Symfony\Component\HttpFoundation\Session;
final class SessionBagProxy implements SessionBagInterface
{
    private $bag;
    private $data;
    private $usageIndex;
    public function __construct(SessionBagInterface $bag, array &$data, &$usageIndex)
    {
        $this->bag = $bag;
        $this->data = &$data;
        $this->usageIndex = &$usageIndex;
    }
    public function getBag()
    {
        ++$this->usageIndex;
        return $this->bag;
    }
    public function isEmpty()
    {
        if (!isset($this->data[$this->bag->getStorageKey()])) {
            return true;
        }
        ++$this->usageIndex;
        return empty($this->data[$this->bag->getStorageKey()]);
    }
    public function getName()
    {
        return $this->bag->getName();
    }
    public function initialize(array &$array)
    {
        ++$this->usageIndex;
        $this->data[$this->bag->getStorageKey()] = &$array;
        $this->bag->initialize($array);
    }
    public function getStorageKey()
    {
        return $this->bag->getStorageKey();
    }
    public function clear()
    {
        return $this->bag->clear();
    }
}
