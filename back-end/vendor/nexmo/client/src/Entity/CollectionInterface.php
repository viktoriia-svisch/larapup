<?php
namespace Nexmo\Entity;
interface CollectionInterface extends \Countable, \Iterator
{
    public static function getCollectionName();
    public static function getCollectionPath();
    public function hydrateEntity($data, $idOrEntity);
}
