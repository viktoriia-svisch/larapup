<?php
namespace Nexmo\Entity;
trait HasEntityTrait
{
    protected $entity;
    public function setEntity($entity)
    {
        $this->entity = $entity;
    }
    public function getEntity()
    {
        return $this->entity;
    }
}
