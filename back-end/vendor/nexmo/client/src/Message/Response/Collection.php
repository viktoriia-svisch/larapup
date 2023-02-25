<?php
namespace Nexmo\Message\Response;
use Nexmo\Client\Response\Response;
use Nexmo\Client\Response\Error;
use Nexmo\Client\Response\ResponseInterface;
class Collection extends Response implements ResponseInterface, \Countable, \Iterator
{
    protected $count;
    protected $data;
    protected $messages = array();
    protected $position = 0;
    public function __construct(array $data)
    {
        $this->expected = array('message-count', 'messages');
        $return = parent::__construct($data);
        $this->count = $data['message-count'];
        if(count($data['messages']) != $data['message-count']){
            throw new \RuntimeException('invalid message count');
        }
        foreach($data['messages'] as $message){
            if(0 != $message['status']){
                $this->messages[] = new Error($message);
            } else {
                $this->messages[] = new Message($message);
            }
        }
        $this->data = $data;
        return $return;
    }
    public function getMessages()
    {
        return $this->messages;
    }
    public function isSuccess()
    {
        foreach($this->messages as $message){
            if($message instanceof Error){
                return false;
            }
        }
        return true;
    }
    public function count()
    {
        return $this->count;
    }
    public function current()
    {
        return $this->messages[$this->position];
    }
    public function next()
    {
        $this->position++;
    }
    public function key()
    {
        return $this->position;
    }
    public function valid()
    {
        return $this->position < $this->count;
    }
    public function rewind()
    {
        $this->position = 0;
    }
}
