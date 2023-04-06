<?php
namespace Nexmo\Entity;
trait JsonSerializableTrait
{
    public function getRequestData($sent = true)
    {
        if(!($this instanceof EntityInterface)){
            throw new \Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }
        if(!($this instanceof \JsonSerializable)){
            throw new \Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                \JsonSerializable::class
            ));
        }
        if($sent && ($request = $this->getRequest())){
        }
        return $this->jsonSerialize();
    }
}
