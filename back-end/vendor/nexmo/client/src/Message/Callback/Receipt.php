<?php
namespace Nexmo\Message\Callback;
use Nexmo\Client\Callback\Callback;
class Receipt extends Callback
{
    protected $expected = array(
        'err-code',
        'message-timestamp',
        'msisdn',
        'network-code',
        'price',
        'scts',
        'status',
        'to'
    );
    public function __construct(array $data)
    {
        $data = array_merge(array('client-ref' => null), $data);
        parent::__construct($data);
    }
    public function getErrorCode()
    {
        return (int) $this->data['err-code'];
    }
    public function getNetwork()
    {
        return (string) $this->data['network-code'];
    }
    public function getId()
    {
        return (string) $this->data['messageId'];
    }
    public function getReceiptFrom()
    {
        return (string) $this->data['msisdn'];
    }
    public function getTo()
    {
        return $this->getReceiptFrom();
    }
    public function getReceiptTo()
    {
        return (string) $this->data['to'];
    }
    public function getFrom()
    {
        return $this->getReceiptTo();
    }
    public function getStatus()
    {
        return (string) $this->data['status'];
    }
    public function getPrice()
    {
        return (string) $this->data['price'];
    }
    public function getTimestamp()
    {
        $date = \DateTime::createFromFormat('ymdHi', $this->data['scts']);
        if($date){
            return $date;
        }
        throw new \UnexpectedValueException('could not parse message timestamp');
    }
    public function getSent()
    {
        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->data['message-timestamp']);
        if($date){
            return $date;
        }
        throw new \UnexpectedValueException('could not parse message timestamp');
    }
    public function getClientRef()
    {
        return $this->data['client-ref'];
    }
}
