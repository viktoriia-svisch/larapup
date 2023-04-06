<?php
namespace Symfony\Component\HttpFoundation\Session;
interface SessionBagInterface
{
    public function getName();
    public function initialize(array &$array);
    public function getStorageKey();
    public function clear();
}
