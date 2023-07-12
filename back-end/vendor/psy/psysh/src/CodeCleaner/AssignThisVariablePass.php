<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use Psy\Exception\FatalErrorException;
class AssignThisVariablePass extends CodeCleanerPass
{
    public function enterNode(Node $node)
    {
        if ($node instanceof Assign && $node->var instanceof Variable && $node->var->name === 'this') {
            throw new FatalErrorException('Cannot re-assign $this', 0, E_ERROR, null, $node->getLine());
        }
    }
}
