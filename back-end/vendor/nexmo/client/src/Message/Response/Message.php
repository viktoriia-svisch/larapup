<?php
namespace Nexmo\Message\Response;
use Nexmo\Client\Response\Response;
use Nexmo\Client\Response\ResponseInterface;
use Nexmo\Message\Callback\Receipt;
class Message extends Response implements ResponseInterface
{
    protected $receipt;
    public function __construct(Array $data, Receipt $receipt = null)
    {
        $this->expected = array(
            'status',
            'message-id',
            'to',
            'message-price',
            'network'
        );
        $data = array_merge(array('client-ref' => null, 'remaining-balance' => null), $data);
        $return = parent::__construct($data);
        if(!$receipt){
            return $return;
        }
        if($receipt->getId() != $this->getId()){
            throw new \UnexpectedValueException('receipt id must match message id');
        }
        $this->receipt = $receipt;
        return $receipt;
    }
    public function getStatus()
    {
        return (int) $this->data['status'];
    }
    public function getId()
    {
        return (string) $this->data['message-id'];
    }
    public function getTo()
    {
        return (string) $this->data['to'];
    }
    public function getBalance()
    {
        return (string) $this->data['remaining-balance'];
    }
    public function getPrice()
    {
        return (string) $this->data['message-price'];
    }
    public function getNetwork()
    {
        return (string) $this->data['network'];
    }
    public function getClientRef()
    {
        return (string) $this->data['client-ref'];
    }
    public function getReceipt()
    {
        return $this->receipt;
    }
    public function hasReceipt()
    {
        return $this->receipt instanceof Receipt;
    }
}
