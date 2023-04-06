<?php
namespace PharIo\Manifest;
class CopyrightInformation {
    private $authors;
    private $license;
    public function __construct(AuthorCollection $authors, License $license) {
        $this->authors = $authors;
        $this->license = $license;
    }
    public function getAuthors() {
        return $this->authors;
    }
    public function getLicense() {
        return $this->license;
    }
}
