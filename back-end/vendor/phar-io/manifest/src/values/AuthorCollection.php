<?php
namespace PharIo\Manifest;
class AuthorCollection implements \Countable, \IteratorAggregate {
    private $authors = [];
    public function add(Author $author) {
        $this->authors[] = $author;
    }
    public function getAuthors() {
        return $this->authors;
    }
    public function count() {
        return count($this->authors);
    }
    public function getIterator() {
        return new AuthorCollectionIterator($this);
    }
}
