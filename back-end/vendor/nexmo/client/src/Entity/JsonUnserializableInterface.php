<?php
namespace Nexmo\Entity;
interface JsonUnserializableInterface
{
    public function jsonUnserialize(array $json);
}
