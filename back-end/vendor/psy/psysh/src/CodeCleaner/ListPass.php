<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\List_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use Psy\Exception\ParseErrorException;
class ListPass extends CodeCleanerPass
{
    private $atLeastPhp71;
    public function __construct()
    {
        $this->atLeastPhp71 = \version_compare(PHP_VERSION, '7.1', '>=');
    }
    public function enterNode(Node $node)
    {
        if (!$node instanceof Assign) {
            return;
        }
        if (!$node->var instanceof Array_ && !$node->var instanceof List_) {
            return;
        }
        if (!$this->atLeastPhp71 && $node->var instanceof Array_) {
            $msg = "syntax error, unexpected '='";
            throw new ParseErrorException($msg, $node->expr->getLine());
        }
        $items = isset($node->var->items) ? $node->var->items : $node->var->vars;
        if ($items === [] || $items === [null]) {
            throw new ParseErrorException('Cannot use empty list', $node->var->getLine());
        }
        $itemFound = false;
        foreach ($items as $item) {
            if ($item === null) {
                continue;
            }
            $itemFound = true;
            if (!$this->atLeastPhp71 && $item instanceof ArrayItem && $item->key !== null) {
                $msg = 'Syntax error, unexpected T_CONSTANT_ENCAPSED_STRING, expecting \',\' or \')\'';
                throw new ParseErrorException($msg, $item->key->getLine());
            }
            if (!self::isValidArrayItem($item)) {
                $msg = 'Assignments can only happen to writable values';
                throw new ParseErrorException($msg, $item->getLine());
            }
        }
        if (!$itemFound) {
            throw new ParseErrorException('Cannot use empty list');
        }
    }
    private static function isValidArrayItem(Expr $item)
    {
        $value = ($item instanceof ArrayItem) ? $item->value : $item;
        while ($value instanceof ArrayDimFetch || $value instanceof PropertyFetch) {
            $value = $value->var;
        }
        return $value instanceof Variable || $value instanceof MethodCall || $value instanceof FuncCall;
    }
}
