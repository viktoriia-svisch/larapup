<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use Psy\Exception\RuntimeException;
class LeavePsyshAlonePass extends CodeCleanerPass
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Variable && $node->name === '__psysh__') {
            throw new RuntimeException('Don\'t mess with $__psysh__; bad things will happen');
        }
    }
}
