<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Scalar;
use PhpParser\Node\Scalar\Encapsed;
use Psy\Exception\FatalErrorException;
class InstanceOfPass extends CodeCleanerPass
{
    const EXCEPTION_MSG = 'instanceof expects an object instance, constant given';
    public function enterNode(Node $node)
    {
        if (!$node instanceof Instanceof_) {
            return;
        }
        if (($node->expr instanceof Scalar && !$node->expr instanceof Encapsed) || $node->expr instanceof ConstFetch) {
            throw new FatalErrorException(self::EXCEPTION_MSG, 0, E_ERROR, null, $node->getLine());
        }
    }
}
