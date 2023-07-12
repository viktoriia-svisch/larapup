<?php
namespace PharIo\Manifest;
class ExtElementCollection extends ElementCollection {
    public function current() {
        return new ExtElement(
            $this->getCurrentElement()
        );
    }
}
