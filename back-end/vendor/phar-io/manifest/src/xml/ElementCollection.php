<?php
namespace PharIo\Manifest;
use DOMElement;
use DOMNodeList;
abstract class ElementCollection implements \Iterator {
    private $nodeList;
    private $position;
    public function __construct(DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
        $this->position = 0;
    }
    abstract public function current();
    protected function getCurrentElement() {
        return $this->nodeList->item($this->position);
    }
    public function next() {
        $this->position++;
    }
    public function key() {
        return $this->position;
    }
    public function valid() {
        return $this->position < $this->nodeList->length;
    }
    public function rewind() {
        $this->position = 0;
    }
}
