<?php
namespace PharIo\Manifest;
class BundledComponentCollectionIterator implements \Iterator {
    private $bundledComponents = [];
    private $position;
    public function __construct(BundledComponentCollection $bundledComponents) {
        $this->bundledComponents = $bundledComponents->getBundledComponents();
    }
    public function rewind() {
        $this->position = 0;
    }
    public function valid() {
        return $this->position < count($this->bundledComponents);
    }
    public function key() {
        return $this->position;
    }
    public function current() {
        return $this->bundledComponents[$this->position];
    }
    public function next() {
        $this->position++;
    }
}
