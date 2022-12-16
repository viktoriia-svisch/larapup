<?php
namespace Psy\CodeCleaner;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
class NoReturnValue
{
    public static function create()
    {
        return new New_(new FullyQualifiedName('Psy\CodeCleaner\NoReturnValue'));
    }
}
