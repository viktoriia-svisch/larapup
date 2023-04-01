<?php
namespace PharIo\Manifest;
class RequirementCollectionIterator implements \Iterator {
    private $requirements = [];
    private $position;
    public function __construct(RequirementCollection $requirements) {
        $this->requirements = $requirements->getRequirements();
    }
    public function rewind() {
        $this->position = 0;
    }
    public function valid() {
        return $this->position < count($this->requirements);
    }
    public function key() {
        return $this->position;
    }
    public function current() {
        return $this->requirements[$this->position];
    }
    public function next() {
        $this->position++;
    }
}
