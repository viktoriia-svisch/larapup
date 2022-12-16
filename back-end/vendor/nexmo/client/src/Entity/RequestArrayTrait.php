<?php
namespace Nexmo\Entity;
trait RequestArrayTrait
{
    protected $requestData = [];
    public function getRequestData($sent = true)
    {
        if(!($this instanceof EntityInterface)){
            throw new \Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }
        if($sent && ($request = $this->getRequest())){
            $query = [];
            parse_str($request->getUri()->getQuery(), $query);
            return $query;
        }
        if (method_exists($this, 'preGetRequestDataHook')) {
            $this->preGetRequestDataHook();
        }
        return $this->requestData;
    }    
    protected function setRequestData($name, $value)
    {
        if(!($this instanceof EntityInterface)){
            throw new \Exception(sprintf(
                '%s can only be used if the class implements %s',
                __TRAIT__,
                EntityInterface::class
            ));
        }
        if($this->getResponse()){
            throw new \RuntimeException(sprintf(
                'can not set request parameter `%s` for `%s` after API request has be made',
                $name,
                get_class($this)
            ));
        }
        $this->requestData[$name] = $value;
        return $this;
    }
}
