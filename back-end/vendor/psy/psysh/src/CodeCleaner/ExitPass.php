<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
class ExitPass extends CodeCleanerPass
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Exit_) {
            return new StaticCall(new FullyQualifiedName('Psy\Exception\BreakException'), 'exitShell');
        }
    }
}
