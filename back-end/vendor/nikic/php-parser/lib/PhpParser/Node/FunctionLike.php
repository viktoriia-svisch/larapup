<?php declare(strict_types=1);
namespace PhpParser\Node;
use PhpParser\Node;
interface FunctionLike extends Node
{
    public function returnsByRef() : bool;
    public function getParams() : array;
    public function getReturnType();
    public function getStmts();
}
