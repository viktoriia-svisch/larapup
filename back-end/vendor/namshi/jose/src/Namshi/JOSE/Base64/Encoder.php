<?php
namespace Namshi\JOSE\Base64;
interface Encoder
{
    public function encode($data);
    public function decode($data);
}
