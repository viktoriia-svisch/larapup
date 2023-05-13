<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified as FullyQualifiedName;
use PhpParser\Node\Scalar\LNumber;
use Psy\Exception\ErrorException;
use Psy\Exception\FatalErrorException;
use Psy\Shell;
class RequirePass extends CodeCleanerPass
{
    private static $requireTypes = [Include_::TYPE_REQUIRE, Include_::TYPE_REQUIRE_ONCE];
    public function enterNode(Node $origNode)
    {
        if (!$this->isRequireNode($origNode)) {
            return;
        }
        $node = clone $origNode;
        $node->expr = new StaticCall(
            new FullyQualifiedName('Psy\CodeCleaner\RequirePass'),
            'resolve',
            [new Arg($origNode->expr), new Arg(new LNumber($origNode->getLine()))],
            $origNode->getAttributes()
        );
        return $node;
    }
    public static function resolve($file, $lineNumber = null)
    {
        $file = (string) $file;
        if ($file === '') {
            if (E_WARNING & \error_reporting()) {
                ErrorException::throwException(E_WARNING, 'Filename cannot be empty', null, $lineNumber);
            }
        }
        if ($file === '' || !\stream_resolve_include_path($file)) {
            $msg = \sprintf("Failed opening required '%s'", $file);
            throw new FatalErrorException($msg, 0, E_ERROR, null, $lineNumber);
        }
        return $file;
    }
    private function isRequireNode(Node $node)
    {
        return $node instanceof Include_ && \in_array($node->type, self::$requireTypes);
    }
}
