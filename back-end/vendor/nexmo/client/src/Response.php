<?php
namespace Nexmo;
use Nexmo\Response\Message;
class Response implements \Countable, \Iterator
{
    protected $data;
    protected $messages = array();
    protected $position = 0;
    public function __construct($data)
    {
        if(!is_string($data)){
            throw new \InvalidArgumentException('expected response data to be a string');
        }
        $this->data = json_decode($data, true);
    }
    public function getMessages()
    {
        if(!isset($this->data['messages'])){
            return array();
        }
        return $this->data['messages'];
    }
    public function count()
    {
        return $this->data['message-count'];
    }
    public function current()
    {
        if(!isset($this->messages[$this->position])){
            $this->messages[$this->position] = new Message($this->data['messages'][$this->position]);
        }
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
        return isset($this->data['messages'][$this->position]);
    }
    public function rewind()
    {
        $this->position = 0;
    }
    public function toArray()
    {
        return $this->data;
    }
}
