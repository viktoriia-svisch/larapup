<?php declare(strict_types=1);
namespace PhpParser;
interface Builder
{
    public function getNode() : Node;
}
