<?php
namespace Nexmo\Entity;
class EmptyFilter implements FilterInterface
{
    public function getQuery()
    {
        return [];
    }
}
