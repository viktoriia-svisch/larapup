<?php
namespace PharIo\Manifest;
class License {
    private $name;
    private $url;
    public function __construct($name, Url $url) {
        $this->name = $name;
        $this->url  = $url;
    }
    public function getName() {
        return $this->name;
    }
    public function getUrl() {
        return $this->url;
    }
}
