<?php
namespace Symfony\Component\HttpFoundation\Session\Storage;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
interface SessionStorageInterface
{
    public function start();
    public function isStarted();
    public function getId();
    public function setId($id);
    public function getName();
    public function setName($name);
    public function regenerate($destroy = false, $lifetime = null);
    public function save();
    public function clear();
    public function getBag($name);
    public function registerBag(SessionBagInterface $bag);
    public function getMetadataBag();
}
