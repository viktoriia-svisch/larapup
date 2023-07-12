<?php
namespace PharIo\Manifest;
class ComponentElementCollection extends ElementCollection {
    public function current() {
        return new ComponentElement(
            $this->getCurrentElement()
        );
    }
}
