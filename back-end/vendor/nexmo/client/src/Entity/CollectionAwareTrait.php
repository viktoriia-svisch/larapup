<?php
namespace Nexmo\Entity;
trait CollectionAwareTrait
{
    protected $collection;
    public function setCollection(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }
    public function getCollection()
    {
        if(!isset($this->collection)){
            throw new \RuntimeException('missing collection');
        }
        return $this->collection;
    }
}
