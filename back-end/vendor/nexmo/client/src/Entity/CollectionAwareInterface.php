<?php
namespace Nexmo\Entity;
interface CollectionAwareInterface
{
    public function setCollection(CollectionInterface $collection);
    public function getCollection();
}
