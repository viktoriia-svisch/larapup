<?php
namespace PharIo\Manifest;
class BundledComponentCollection implements \Countable, \IteratorAggregate {
    private $bundledComponents = [];
    public function add(BundledComponent $bundledComponent) {
        $this->bundledComponents[] = $bundledComponent;
    }
    public function getBundledComponents() {
        return $this->bundledComponents;
    }
    public function count() {
        return count($this->bundledComponents);
    }
    public function getIterator() {
        return new BundledComponentCollectionIterator($this);
    }
}
