<?php
namespace PharIo\Manifest;
class AuthorCollectionIterator implements \Iterator {
    private $authors = [];
    private $position;
    public function __construct(AuthorCollection $authors) {
        $this->authors = $authors->getAuthors();
    }
    public function rewind() {
        $this->position = 0;
    }
    public function valid() {
        return $this->position < count($this->authors);
    }
    public function key() {
        return $this->position;
    }
    public function current() {
        return $this->authors[$this->position];
    }
    public function next() {
        $this->position++;
    }
}
