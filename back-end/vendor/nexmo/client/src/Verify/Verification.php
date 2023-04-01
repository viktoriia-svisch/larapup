<?php
namespace Nexmo\Verify;
use Nexmo\Client\Exception\Request as RequestException;
use Nexmo\Entity\JsonResponseTrait;
use Nexmo\Entity\Psr7Trait;
use Nexmo\Entity\RequestArrayTrait;
class Verification implements VerificationInterface, \ArrayAccess, \Serializable
{
    use Psr7Trait;
    use RequestArrayTrait;
    use JsonResponseTrait;
    const FAILED = 'FAILED';
    const SUCCESSFUL = 'SUCCESSFUL';
    const EXPIRED = 'EXPIRED';
    const IN_PROGRESS = 'IN PROGRESS';
    protected $dirty = true;
    protected $client;
    public function __construct($idOrNumber, $brand = null, $additional = [])
    {
        if(is_null($brand)){
            $this->dirty = false;
            $this->requestData['request_id'] = $idOrNumber;
        } else {
            $this->dirty = true;
            $this->requestData['number'] = $idOrNumber;
            $this->requestData['brand']  = $brand;
            $this->requestData = array_merge($this->requestData, $additional);
        }
    }
    public function setClient(Client $client)
    {
        $this->client = $client;
        return $this;
    }
    protected function useClient()
    {
        if(isset($this->client)){
            return $this->client;
        }
        throw new \RuntimeException('can not act on the verification directly unless a verify client has been set');
    }
    public function check($code, $ip = null)
    {
        try {
            $this->useClient()->check($this, $code, $ip);
            return true;
        } catch(RequestException $e) {
            if($e->getCode() == 16 || $e->getCode() == 17){
                return false;
            }
            throw $e;
        }
    }
    public function cancel()
    {
        $this->useClient()->cancel($this);
    }
    public function trigger()
    {
        $this->useClient()->trigger($this);
    }
    public function sync()
    {
        $this->useClient()->search($this);
    }
    public function isDirty()
    {
        return $this->dirty;
    }
    public function setCountry($country)
    {
        return $this->setRequestData('country', $country);
    }
    public function setSenderId($id)
    {
        return $this->setRequestData('sender_id', $id);
    }
    public function setCodeLength($length)
    {
        return $this->setRequestData('code_length', $length);
    }
    public function setLanguage($language)
    {
        return $this->setRequestData('lg', $language);
    }
    public function setRequireType($type)
    {
        return $this->setRequestData('require_type', $type);
    }
    public function setPinExpiry($time)
    {
        return $this->setRequestData('pin_expiry', $time);
    }
    public function setWaitTime($time)
    {
        return $this->setRequestData('next_event_wait', $time);
    }
    public function getRequestId()
    {
        return $this->proxyArrayAccess('request_id');
    }
    public function getNumber()
    {
        return $this->proxyArrayAccess('number');
    }
    public function getAccountId()
    {
        return $this->proxyArrayAccess('account_id');
    }
    public function getSenderId()
    {
        return $this->proxyArrayAccess('sender_id');
    }
    public function getPrice()
    {
        return $this->proxyArrayAccess('price');
    }
    public function getCurrency()
    {
        return $this->proxyArrayAccess('currency');
    }
    public function getStatus()
    {
        return $this->proxyArrayAccess('status');
    }
    public function getChecks()
    {
        $checks = $this->proxyArrayAccess('checks');
        if(!$checks){
            return [];
        }
        foreach($checks as $i => $check) {
            $checks[$i] = new Check($check);
        }
        return $checks;
    }
    public function getSubmitted()
    {
        return $this->proxyArrayAccessDate('date_submitted');
    }
    public function getFinalized()
    {
        return $this->proxyArrayAccessDate('date_finalized');
    }
    public function getFirstEvent()
    {
        return $this->proxyArrayAccessDate('first_event_date');
    }
    public function getLastEvent()
    {
        return $this->proxyArrayAccessDate('last_event_date');
    }
    protected function proxyArrayAccessDate($param)
    {
        $date = $this->proxyArrayAccess($param);
        if($date) {
            return new \DateTime($date);
        }
    }
    protected function proxyArrayAccess($param)
    {
        if(isset($this[$param])){
            return $this[$param];
        }
    }
    public function offsetExists($offset)
    {
        $response = $this->getResponseData();
        $request  = $this->getRequestData();
        $dirty    = $this->requestData;
        return isset($response[$offset]) || isset($request[$offset]) || isset($dirty[$offset]);
    }
    public function offsetGet($offset)
    {
        $response = $this->getResponseData();
        $request  = $this->getRequestData();
        $dirty    = $this->requestData;
        if(isset($response[$offset])){
            return $response[$offset];
        }
        if(isset($request[$offset])){
            return $request[$offset];
        }
        if(isset($dirty[$offset])){
            return $dirty[$offset];
        }
    }
    public function offsetSet($offset, $value)
    {
        throw $this->getReadOnlyException($offset);
    }
    public function offsetUnset($offset)
    {
        throw $this->getReadOnlyException($offset);
    }
    protected function getReadOnlyException($offset)
    {
        return new \RuntimeException(sprintf(
            'can not modify `%s` using array access',
            $offset
        ));
    }
    public function serialize()
    {
        $data = [
            'requestData'  => $this->requestData
        ];
        if($request = $this->getRequest()){
            $data['request'] = \Zend\Diactoros\Request\Serializer::toString($request);
        }
        if($response = $this->getResponse()){
            $data['response'] = \Zend\Diactoros\Response\Serializer::toString($response);
        }
        return serialize($data);
    }
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->requestData = $data['requestData'];
        if(isset($data['request'])){
            $this->request = \Zend\Diactoros\Request\Serializer::fromString($data['request']);
        }
        if(isset($data['response'])){
            $this->response = \Zend\Diactoros\Response\Serializer::fromString($data['response']);
        }
    }
}
