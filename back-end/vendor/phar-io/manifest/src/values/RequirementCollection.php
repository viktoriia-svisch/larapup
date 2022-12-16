<?php
namespace PharIo\Manifest;
class RequirementCollection implements \Countable, \IteratorAggregate {
    private $requirements = [];
    public function add(Requirement $requirement) {
        $this->requirements[] = $requirement;
    }
    public function getRequirements() {
        return $this->requirements;
    }
    public function count() {
        return count($this->requirements);
    }
    public function getIterator() {
        return new RequirementCollectionIterator($this);
    }
}
