<?php
namespace Opis\Closure;
class SelfReference
{
    public $hash;
    public function __construct($hash)
    {
        $this->hash = $hash;
    }
}
