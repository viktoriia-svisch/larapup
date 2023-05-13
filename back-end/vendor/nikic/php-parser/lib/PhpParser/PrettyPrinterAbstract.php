<?php declare(strict_types=1);
namespace PhpParser;
use PhpParser\Internal\DiffElem;
use PhpParser\Internal\PrintableNewAnonClassNode;
use PhpParser\Internal\TokenStream;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\Cast;
use PhpParser\Node\Scalar;
use PhpParser\Node\Stmt;
abstract class PrettyPrinterAbstract
{
    const FIXUP_PREC_LEFT       = 0; 
    const FIXUP_PREC_RIGHT      = 1; 
    const FIXUP_CALL_LHS        = 2; 
    const FIXUP_DEREF_LHS       = 3; 
    const FIXUP_BRACED_NAME     = 4; 
    const FIXUP_VAR_BRACED_NAME = 5; 
    const FIXUP_ENCAPSED        = 6; 
    protected $precedenceMap = [
        BinaryOp\Pow::class            => [  0,  1],
        Expr\BitwiseNot::class         => [ 10,  1],
        Expr\PreInc::class             => [ 10,  1],
        Expr\PreDec::class             => [ 10,  1],
        Expr\PostInc::class            => [ 10, -1],
        Expr\PostDec::class            => [ 10, -1],
        Expr\UnaryPlus::class          => [ 10,  1],
        Expr\UnaryMinus::class         => [ 10,  1],
        Cast\Int_::class               => [ 10,  1],
        Cast\Double::class             => [ 10,  1],
        Cast\String_::class            => [ 10,  1],
        Cast\Array_::class             => [ 10,  1],
        Cast\Object_::class            => [ 10,  1],
        Cast\Bool_::class              => [ 10,  1],
        Cast\Unset_::class             => [ 10,  1],
        Expr\ErrorSuppress::class      => [ 10,  1],
        Expr\Instanceof_::class        => [ 20,  0],
        Expr\BooleanNot::class         => [ 30,  1],
        BinaryOp\Mul::class            => [ 40, -1],
        BinaryOp\Div::class            => [ 40, -1],
        BinaryOp\Mod::class            => [ 40, -1],
        BinaryOp\Plus::class           => [ 50, -1],
        BinaryOp\Minus::class          => [ 50, -1],
        BinaryOp\Concat::class         => [ 50, -1],
        BinaryOp\ShiftLeft::class      => [ 60, -1],
        BinaryOp\ShiftRight::class     => [ 60, -1],
        BinaryOp\Smaller::class        => [ 70,  0],
        BinaryOp\SmallerOrEqual::class => [ 70,  0],
        BinaryOp\Greater::class        => [ 70,  0],
        BinaryOp\GreaterOrEqual::class => [ 70,  0],
        BinaryOp\Equal::class          => [ 80,  0],
        BinaryOp\NotEqual::class       => [ 80,  0],
        BinaryOp\Identical::class      => [ 80,  0],
        BinaryOp\NotIdentical::class   => [ 80,  0],
        BinaryOp\Spaceship::class      => [ 80,  0],
        BinaryOp\BitwiseAnd::class     => [ 90, -1],
        BinaryOp\BitwiseXor::class     => [100, -1],
        BinaryOp\BitwiseOr::class      => [110, -1],
        BinaryOp\BooleanAnd::class     => [120, -1],
        BinaryOp\BooleanOr::class      => [130, -1],
        BinaryOp\Coalesce::class       => [140,  1],
        Expr\Ternary::class            => [150, -1],
        Expr\Assign::class             => [160,  1],
        Expr\AssignRef::class          => [160,  1],
        AssignOp\Plus::class           => [160,  1],
        AssignOp\Minus::class          => [160,  1],
        AssignOp\Mul::class            => [160,  1],
        AssignOp\Div::class            => [160,  1],
        AssignOp\Concat::class         => [160,  1],
        AssignOp\Mod::class            => [160,  1],
        AssignOp\BitwiseAnd::class     => [160,  1],
        AssignOp\BitwiseOr::class      => [160,  1],
        AssignOp\BitwiseXor::class     => [160,  1],
        AssignOp\ShiftLeft::class      => [160,  1],
        AssignOp\ShiftRight::class     => [160,  1],
        AssignOp\Pow::class            => [160,  1],
        AssignOp\Coalesce::class       => [160,  1],
        Expr\YieldFrom::class          => [165,  1],
        Expr\Print_::class             => [168,  1],
        BinaryOp\LogicalAnd::class     => [170, -1],
        BinaryOp\LogicalXor::class     => [180, -1],
        BinaryOp\LogicalOr::class      => [190, -1],
        Expr\Include_::class           => [200, -1],
    ];
    protected $indentLevel;
    protected $nl;
    protected $docStringEndToken;
    protected $canUseSemicolonNamespaces;
    protected $options;
    protected $origTokens;
    protected $nodeListDiffer;
    protected $labelCharMap;
    protected $fixupMap;
    protected $removalMap;
    protected $insertionMap;
    protected $listInsertionMap;
    protected $modifierChangeMap;
    public function __construct(array $options = []) {
        $this->docStringEndToken = '_DOC_STRING_END_' . mt_rand();
        $defaultOptions = ['shortArraySyntax' => false];
        $this->options = $options + $defaultOptions;
    }
    protected function resetState() {
        $this->indentLevel = 0;
        $this->nl = "\n";
        $this->origTokens = null;
    }
    protected function setIndentLevel(int $level) {
        $this->indentLevel = $level;
        $this->nl = "\n" . \str_repeat(' ', $level);
    }
    protected function indent() {
        $this->indentLevel += 4;
        $this->nl .= '    ';
    }
    protected function outdent() {
        assert($this->indentLevel >= 4);
        $this->indentLevel -= 4;
        $this->nl = "\n" . str_repeat(' ', $this->indentLevel);
    }
    public function prettyPrint(array $stmts) : string {
        $this->resetState();
        $this->preprocessNodes($stmts);
        return ltrim($this->handleMagicTokens($this->pStmts($stmts, false)));
    }
    public function prettyPrintExpr(Expr $node) : string {
        $this->resetState();
        return $this->handleMagicTokens($this->p($node));
    }
    public function prettyPrintFile(array $stmts) : string {
        if (!$stmts) {
            return "<?php\n\n";
        }
        $p = "<?php\n\n" . $this->prettyPrint($stmts);
        if ($stmts[0] instanceof Stmt\InlineHTML) {
            $p = preg_replace('/^<\?php\s+\?>\n?/', '', $p);
        }
        if ($stmts[count($stmts) - 1] instanceof Stmt\InlineHTML) {
            $p = preg_replace('/<\?php$/', '', rtrim($p));
        }
        return $p;
    }
    protected function preprocessNodes(array $nodes) {
        $this->canUseSemicolonNamespaces = true;
        foreach ($nodes as $node) {
            if ($node instanceof Stmt\Namespace_ && null === $node->name) {
                $this->canUseSemicolonNamespaces = false;
                break;
            }
        }
    }
    protected function handleMagicTokens(string $str) : string {
        $str = str_replace($this->docStringEndToken . ";\n", ";\n", $str);
        $str = str_replace($this->docStringEndToken, "\n", $str);
        return $str;
    }
    protected function pStmts(array $nodes, bool $indent = true) : string {
        if ($indent) {
            $this->indent();
        }
        $result = '';
        foreach ($nodes as $node) {
            $comments = $node->getComments();
            if ($comments) {
                $result .= $this->nl . $this->pComments($comments);
                if ($node instanceof Stmt\Nop) {
                    continue;
                }
            }
            $result .= $this->nl . $this->p($node);
        }
        if ($indent) {
            $this->outdent();
        }
        return $result;
    }
    protected function pInfixOp(string $class, Node $leftNode, string $operatorString, Node $rightNode) : string {
        list($precedence, $associativity) = $this->precedenceMap[$class];
        return $this->pPrec($leftNode, $precedence, $associativity, -1)
             . $operatorString
             . $this->pPrec($rightNode, $precedence, $associativity, 1);
    }
    protected function pPrefixOp(string $class, string $operatorString, Node $node) : string {
        list($precedence, $associativity) = $this->precedenceMap[$class];
        return $operatorString . $this->pPrec($node, $precedence, $associativity, 1);
    }
    protected function pPostfixOp(string $class, Node $node, string $operatorString) : string {
        list($precedence, $associativity) = $this->precedenceMap[$class];
        return $this->pPrec($node, $precedence, $associativity, -1) . $operatorString;
    }
    protected function pPrec(Node $node, int $parentPrecedence, int $parentAssociativity, int $childPosition) : string {
        $class = \get_class($node);
        if (isset($this->precedenceMap[$class])) {
            $childPrecedence = $this->precedenceMap[$class][0];
            if ($childPrecedence > $parentPrecedence
                || ($parentPrecedence === $childPrecedence && $parentAssociativity !== $childPosition)
            ) {
                return '(' . $this->p($node) . ')';
            }
        }
        return $this->p($node);
    }
    protected function pImplode(array $nodes, string $glue = '') : string {
        $pNodes = [];
        foreach ($nodes as $node) {
            if (null === $node) {
                $pNodes[] = '';
            } else {
                $pNodes[] = $this->p($node);
            }
        }
        return implode($glue, $pNodes);
    }
    protected function pCommaSeparated(array $nodes) : string {
        return $this->pImplode($nodes, ', ');
    }
    protected function pCommaSeparatedMultiline(array $nodes, bool $trailingComma) : string {
        $this->indent();
        $result = '';
        $lastIdx = count($nodes) - 1;
        foreach ($nodes as $idx => $node) {
            if ($node !== null) {
                $comments = $node->getComments();
                if ($comments) {
                    $result .= $this->nl . $this->pComments($comments);
                }
                $result .= $this->nl . $this->p($node);
            } else {
                $result .= $this->nl;
            }
            if ($trailingComma || $idx !== $lastIdx) {
                $result .= ',';
            }
        }
        $this->outdent();
        return $result;
    }
    protected function pComments(array $comments) : string {
        $formattedComments = [];
        foreach ($comments as $comment) {
            $formattedComments[] = str_replace("\n", $this->nl, $comment->getReformattedText());
        }
        return implode($this->nl, $formattedComments);
    }
    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens) : string {
        $this->initializeNodeListDiffer();
        $this->initializeLabelCharMap();
        $this->initializeFixupMap();
        $this->initializeRemovalMap();
        $this->initializeInsertionMap();
        $this->initializeListInsertionMap();
        $this->initializeModifierChangeMap();
        $this->resetState();
        $this->origTokens = new TokenStream($origTokens);
        $this->preprocessNodes($stmts);
        $pos = 0;
        $result = $this->pArray($stmts, $origStmts, $pos, 0, 'stmts', null, "\n");
        if (null !== $result) {
            $result .= $this->origTokens->getTokenCode($pos, count($origTokens), 0);
        } else {
            $result = "<?php\n" . $this->pStmts($stmts, false);
        }
        return ltrim($this->handleMagicTokens($result));
    }
    protected function pFallback(Node $node) {
        return $this->{'p' . $node->getType()}($node);
    }
    protected function p(Node $node, $parentFormatPreserved = false) : string {
        if (!$this->origTokens) {
            return $this->{'p' . $node->getType()}($node);
        }
        $origNode = $node->getAttribute('origNode');
        if (null === $origNode) {
            return $this->pFallback($node);
        }
        $class = \get_class($node);
        \assert($class === \get_class($origNode));
        $startPos = $origNode->getStartTokenPos();
        $endPos = $origNode->getEndTokenPos();
        \assert($startPos >= 0 && $endPos >= 0);
        $fallbackNode = $node;
        if ($node instanceof Expr\New_ && $node->class instanceof Stmt\Class_) {
            $node = PrintableNewAnonClassNode::fromNewNode($node);
            $origNode = PrintableNewAnonClassNode::fromNewNode($origNode);
        }
        if ($node instanceof Stmt\InlineHTML && !$parentFormatPreserved) {
            return $this->pFallback($fallbackNode);
        }
        $indentAdjustment = $this->indentLevel - $this->origTokens->getIndentationBefore($startPos);
        $type = $node->getType();
        $fixupInfo = $this->fixupMap[$class] ?? null;
        $result = '';
        $pos = $startPos;
        foreach ($node->getSubNodeNames() as $subNodeName) {
            $subNode = $node->$subNodeName;
            $origSubNode = $origNode->$subNodeName;
            if ((!$subNode instanceof Node && $subNode !== null)
                || (!$origSubNode instanceof Node && $origSubNode !== null)
            ) {
                if ($subNode === $origSubNode) {
                    continue;
                }
                if (is_array($subNode) && is_array($origSubNode)) {
                    $listResult = $this->pArray(
                        $subNode, $origSubNode, $pos, $indentAdjustment, $subNodeName,
                        $fixupInfo[$subNodeName] ?? null,
                        $this->listInsertionMap[$type . '->' . $subNodeName] ?? null
                    );
                    if (null === $listResult) {
                        return $this->pFallback($fallbackNode);
                    }
                    $result .= $listResult;
                    continue;
                }
                if (is_int($subNode) && is_int($origSubNode)) {
                    $key = $type . '->' . $subNodeName;
                    if (!isset($this->modifierChangeMap[$key])) {
                        return $this->pFallback($fallbackNode);
                    }
                    $findToken = $this->modifierChangeMap[$key];
                    $result .= $this->pModifiers($subNode);
                    $pos = $this->origTokens->findRight($pos, $findToken);
                    continue;
                }
                return $this->pFallback($fallbackNode);
            }
            $extraLeft = '';
            $extraRight = '';
            if ($origSubNode !== null) {
                $subStartPos = $origSubNode->getStartTokenPos();
                $subEndPos = $origSubNode->getEndTokenPos();
                \assert($subStartPos >= 0 && $subEndPos >= 0);
            } else {
                if ($subNode === null) {
                    continue;
                }
                $key = $type . '->' . $subNodeName;
                if (!isset($this->insertionMap[$key])) {
                    return $this->pFallback($fallbackNode);
                }
                list($findToken, $beforeToken, $extraLeft, $extraRight) = $this->insertionMap[$key];
                if (null !== $findToken) {
                    $subStartPos = $this->origTokens->findRight($pos, $findToken)
                        + (int) !$beforeToken;
                } else {
                    $subStartPos = $pos;
                }
                if (null === $extraLeft && null !== $extraRight) {
                    $subStartPos = $this->origTokens->skipRightWhitespace($subStartPos);
                }
                $subEndPos = $subStartPos - 1;
            }
            if (null === $subNode) {
                $key = $type . '->' . $subNodeName;
                if (!isset($this->removalMap[$key])) {
                    return $this->pFallback($fallbackNode);
                }
                $removalInfo = $this->removalMap[$key];
                if (isset($removalInfo['left'])) {
                    $subStartPos = $this->origTokens->skipLeft($subStartPos - 1, $removalInfo['left']) + 1;
                }
                if (isset($removalInfo['right'])) {
                    $subEndPos = $this->origTokens->skipRight($subEndPos + 1, $removalInfo['right']) - 1;
                }
            }
            $result .= $this->origTokens->getTokenCode($pos, $subStartPos, $indentAdjustment);
            if (null !== $subNode) {
                $result .= $extraLeft;
                $origIndentLevel = $this->indentLevel;
                $this->setIndentLevel($this->origTokens->getIndentationBefore($subStartPos) + $indentAdjustment);
                if (isset($fixupInfo[$subNodeName])
                    && $subNode->getAttribute('origNode') !== $origSubNode
                ) {
                    $fixup = $fixupInfo[$subNodeName];
                    $res = $this->pFixup($fixup, $subNode, $class, $subStartPos, $subEndPos);
                } else {
                    $res = $this->p($subNode, true);
                }
                $this->safeAppend($result, $res);
                $this->setIndentLevel($origIndentLevel);
                $result .= $extraRight;
            }
            $pos = $subEndPos + 1;
        }
        $result .= $this->origTokens->getTokenCode($pos, $endPos + 1, $indentAdjustment);
        return $result;
    }
    protected function pArray(
        array $nodes, array $origNodes, int &$pos, int $indentAdjustment,
        string $subNodeName, $fixup, $insertStr
    ) {
        $diff = $this->nodeListDiffer->diffWithReplacements($origNodes, $nodes);
        $beforeFirstKeepOrReplace = true;
        $delayedAdd = [];
        $lastElemIndentLevel = $this->indentLevel;
        $insertNewline = false;
        if ($insertStr === "\n") {
            $insertStr = '';
            $insertNewline = true;
        }
        if ($subNodeName === 'stmts' && \count($origNodes) === 1 && \count($nodes) !== 1) {
            $startPos = $origNodes[0]->getStartTokenPos();
            $endPos = $origNodes[0]->getEndTokenPos();
            \assert($startPos >= 0 && $endPos >= 0);
            if (!$this->origTokens->haveBraces($startPos, $endPos)) {
                return null;
            }
        }
        $result = '';
        foreach ($diff as $i => $diffElem) {
            $diffType = $diffElem->type;
            $arrItem = $diffElem->new;
            $origArrItem = $diffElem->old;
            if ($diffType === DiffElem::TYPE_KEEP || $diffType === DiffElem::TYPE_REPLACE) {
                $beforeFirstKeepOrReplace = false;
                if ($origArrItem === null || $arrItem === null) {
                    if ($origArrItem === $arrItem) {
                        continue;
                    }
                    return null;
                }
                if (!$arrItem instanceof Node || !$origArrItem instanceof Node) {
                    return null;
                }
                $itemStartPos = $origArrItem->getStartTokenPos();
                $itemEndPos = $origArrItem->getEndTokenPos();
                \assert($itemStartPos >= 0 && $itemEndPos >= 0);
                if ($itemEndPos < $itemStartPos) {
                    assert($origArrItem instanceof Stmt\Nop);
                    continue;
                }
                $origIndentLevel = $this->indentLevel;
                $lastElemIndentLevel = $this->origTokens->getIndentationBefore($itemStartPos) + $indentAdjustment;
                $this->setIndentLevel($lastElemIndentLevel);
                $comments = $arrItem->getComments();
                $origComments = $origArrItem->getComments();
                $commentStartPos = $origComments ? $origComments[0]->getTokenPos() : $itemStartPos;
                \assert($commentStartPos >= 0);
                $commentsChanged = $comments !== $origComments;
                if ($commentsChanged) {
                    $itemStartPos = $commentStartPos;
                }
                if (!empty($delayedAdd)) {
                    $result .= $this->origTokens->getTokenCode(
                        $pos, $commentStartPos, $indentAdjustment);
                    foreach ($delayedAdd as $delayedAddNode) {
                        if ($insertNewline) {
                            $delayedAddComments = $delayedAddNode->getComments();
                            if ($delayedAddComments) {
                                $result .= $this->pComments($delayedAddComments) . $this->nl;
                            }
                        }
                        $this->safeAppend($result, $this->p($delayedAddNode, true));
                        if ($insertNewline) {
                            $result .= $insertStr . $this->nl;
                        } else {
                            $result .= $insertStr;
                        }
                    }
                    $result .= $this->origTokens->getTokenCode(
                        $commentStartPos, $itemStartPos, $indentAdjustment);
                    $delayedAdd = [];
                } else {
                    $result .= $this->origTokens->getTokenCode(
                        $pos, $itemStartPos, $indentAdjustment);
                }
                if ($commentsChanged && $comments) {
                    $result .= $this->pComments($comments) . $this->nl;
                }
            } elseif ($diffType === DiffElem::TYPE_ADD) {
                if (null === $insertStr) {
                    return null;
                }
                if ($insertStr === ', ' && $this->isMultiline($origNodes)) {
                    $insertStr = ',';
                    $insertNewline = true;
                }
                if ($beforeFirstKeepOrReplace) {
                    $delayedAdd[] = $arrItem;
                    continue;
                }
                $itemStartPos = $pos;
                $itemEndPos = $pos - 1;
                $origIndentLevel = $this->indentLevel;
                $this->setIndentLevel($lastElemIndentLevel);
                if ($insertNewline) {
                    $comments = $arrItem->getComments();
                    if ($comments) {
                        $result .= $this->nl . $this->pComments($comments);
                    }
                    $result .= $insertStr . $this->nl;
                } else {
                    $result .= $insertStr;
                }
            } elseif ($diffType === DiffElem::TYPE_REMOVE) {
                if ($i === 0) {
                    return null;
                }
                if (!$origArrItem instanceof Node) {
                    return null;
                }
                $itemEndPos = $origArrItem->getEndTokenPos();
                \assert($itemEndPos >= 0);
                $pos = $itemEndPos + 1;
                continue;
            } else {
                throw new \Exception("Shouldn't happen");
            }
            if (null !== $fixup && $arrItem->getAttribute('origNode') !== $origArrItem) {
                $res = $this->pFixup($fixup, $arrItem, null, $itemStartPos, $itemEndPos);
            } else {
                $res = $this->p($arrItem, true);
            }
            $this->safeAppend($result, $res);
            $this->setIndentLevel($origIndentLevel);
            $pos = $itemEndPos + 1;
        }
        if (!empty($delayedAdd)) {
            return null;
        }
        return $result;
    }
    protected function pFixup(int $fixup, Node $subNode, $parentClass, int $subStartPos, int $subEndPos) : string {
        switch ($fixup) {
            case self::FIXUP_PREC_LEFT:
            case self::FIXUP_PREC_RIGHT:
                if (!$this->origTokens->haveParens($subStartPos, $subEndPos)) {
                    list($precedence, $associativity) = $this->precedenceMap[$parentClass];
                    return $this->pPrec($subNode, $precedence, $associativity,
                        $fixup === self::FIXUP_PREC_LEFT ? -1 : 1);
                }
                break;
            case self::FIXUP_CALL_LHS:
                if ($this->callLhsRequiresParens($subNode)
                    && !$this->origTokens->haveParens($subStartPos, $subEndPos)
                ) {
                    return '(' . $this->p($subNode) . ')';
                }
                break;
            case self::FIXUP_DEREF_LHS:
                if ($this->dereferenceLhsRequiresParens($subNode)
                    && !$this->origTokens->haveParens($subStartPos, $subEndPos)
                ) {
                    return '(' . $this->p($subNode) . ')';
                }
                break;
            case self::FIXUP_BRACED_NAME:
            case self::FIXUP_VAR_BRACED_NAME:
                if ($subNode instanceof Expr
                    && !$this->origTokens->haveBraces($subStartPos, $subEndPos)
                ) {
                    return ($fixup === self::FIXUP_VAR_BRACED_NAME ? '$' : '')
                        . '{' . $this->p($subNode) . '}';
                }
                break;
            case self::FIXUP_ENCAPSED:
                if (!$subNode instanceof Scalar\EncapsedStringPart
                    && !$this->origTokens->haveBraces($subStartPos, $subEndPos)
                ) {
                    return '{' . $this->p($subNode) . '}';
                }
                break;
            default:
                throw new \Exception('Cannot happen');
        }
        return $this->p($subNode);
    }
    protected function safeAppend(string &$str, string $append) {
        if ($str === "") {
            $str = $append;
            return;
        }
        if ($append === "") {
            return;
        }
        if (!$this->labelCharMap[$append[0]]
                || !$this->labelCharMap[$str[\strlen($str) - 1]]) {
            $str .= $append;
        } else {
            $str .= " " . $append;
        }
    }
    protected function callLhsRequiresParens(Node $node) : bool {
        return !($node instanceof Node\Name
            || $node instanceof Expr\Variable
            || $node instanceof Expr\ArrayDimFetch
            || $node instanceof Expr\FuncCall
            || $node instanceof Expr\MethodCall
            || $node instanceof Expr\StaticCall
            || $node instanceof Expr\Array_);
    }
    protected function dereferenceLhsRequiresParens(Node $node) : bool {
        return !($node instanceof Expr\Variable
            || $node instanceof Node\Name
            || $node instanceof Expr\ArrayDimFetch
            || $node instanceof Expr\PropertyFetch
            || $node instanceof Expr\StaticPropertyFetch
            || $node instanceof Expr\FuncCall
            || $node instanceof Expr\MethodCall
            || $node instanceof Expr\StaticCall
            || $node instanceof Expr\Array_
            || $node instanceof Scalar\String_
            || $node instanceof Expr\ConstFetch
            || $node instanceof Expr\ClassConstFetch);
    }
    protected function pModifiers(int $modifiers) {
        return ($modifiers & Stmt\Class_::MODIFIER_PUBLIC    ? 'public '    : '')
             . ($modifiers & Stmt\Class_::MODIFIER_PROTECTED ? 'protected ' : '')
             . ($modifiers & Stmt\Class_::MODIFIER_PRIVATE   ? 'private '   : '')
             . ($modifiers & Stmt\Class_::MODIFIER_STATIC    ? 'static '    : '')
             . ($modifiers & Stmt\Class_::MODIFIER_ABSTRACT  ? 'abstract '  : '')
             . ($modifiers & Stmt\Class_::MODIFIER_FINAL     ? 'final '     : '');
    }
    protected function isMultiline(array $nodes) : bool {
        if (\count($nodes) < 2) {
            return false;
        }
        $pos = -1;
        foreach ($nodes as $node) {
            if (null === $node) {
                continue;
            }
            $endPos = $node->getEndTokenPos() + 1;
            if ($pos >= 0) {
                $text = $this->origTokens->getTokenCode($pos, $endPos, 0);
                if (false === strpos($text, "\n")) {
                    return false;
                }
            }
            $pos = $endPos;
        }
        return true;
    }
    protected function initializeLabelCharMap() {
        if ($this->labelCharMap) return;
        $this->labelCharMap = [];
        for ($i = 0; $i < 256; $i++) {
            $this->labelCharMap[chr($i)] = $i >= 0x7f || ctype_alnum($i);
        }
    }
    protected function initializeNodeListDiffer() {
        if ($this->nodeListDiffer) return;
        $this->nodeListDiffer = new Internal\Differ(function ($a, $b) {
            if ($a instanceof Node && $b instanceof Node) {
                return $a === $b->getAttribute('origNode');
            }
            return $a === null && $b === null;
        });
    }
    protected function initializeFixupMap() {
        if ($this->fixupMap) return;
        $this->fixupMap = [
            Expr\PreInc::class => ['var' => self::FIXUP_PREC_RIGHT],
            Expr\PreDec::class => ['var' => self::FIXUP_PREC_RIGHT],
            Expr\PostInc::class => ['var' => self::FIXUP_PREC_LEFT],
            Expr\PostDec::class => ['var' => self::FIXUP_PREC_LEFT],
            Expr\Instanceof_::class => [
                'expr' => self::FIXUP_PREC_LEFT,
                'class' => self::FIXUP_PREC_RIGHT,
            ],
            Expr\Ternary::class => [
                'cond' => self::FIXUP_PREC_LEFT,
                'else' => self::FIXUP_PREC_RIGHT,
            ],
            Expr\FuncCall::class => ['name' => self::FIXUP_CALL_LHS],
            Expr\StaticCall::class => ['class' => self::FIXUP_DEREF_LHS],
            Expr\ArrayDimFetch::class => ['var' => self::FIXUP_DEREF_LHS],
            Expr\MethodCall::class => [
                'var' => self::FIXUP_DEREF_LHS,
                'name' => self::FIXUP_BRACED_NAME,
            ],
            Expr\StaticPropertyFetch::class => [
                'class' => self::FIXUP_DEREF_LHS,
                'name' => self::FIXUP_VAR_BRACED_NAME,
            ],
            Expr\PropertyFetch::class => [
                'var' => self::FIXUP_DEREF_LHS,
                'name' => self::FIXUP_BRACED_NAME,
            ],
            Scalar\Encapsed::class => [
                'parts' => self::FIXUP_ENCAPSED,
            ],
        ];
        $binaryOps = [
            BinaryOp\Pow::class, BinaryOp\Mul::class, BinaryOp\Div::class, BinaryOp\Mod::class,
            BinaryOp\Plus::class, BinaryOp\Minus::class, BinaryOp\Concat::class,
            BinaryOp\ShiftLeft::class, BinaryOp\ShiftRight::class, BinaryOp\Smaller::class,
            BinaryOp\SmallerOrEqual::class, BinaryOp\Greater::class, BinaryOp\GreaterOrEqual::class,
            BinaryOp\Equal::class, BinaryOp\NotEqual::class, BinaryOp\Identical::class,
            BinaryOp\NotIdentical::class, BinaryOp\Spaceship::class, BinaryOp\BitwiseAnd::class,
            BinaryOp\BitwiseXor::class, BinaryOp\BitwiseOr::class, BinaryOp\BooleanAnd::class,
            BinaryOp\BooleanOr::class, BinaryOp\Coalesce::class, BinaryOp\LogicalAnd::class,
            BinaryOp\LogicalXor::class, BinaryOp\LogicalOr::class,
        ];
        foreach ($binaryOps as $binaryOp) {
            $this->fixupMap[$binaryOp] = [
                'left' => self::FIXUP_PREC_LEFT,
                'right' => self::FIXUP_PREC_RIGHT
            ];
        }
        $assignOps = [
            Expr\Assign::class, Expr\AssignRef::class, AssignOp\Plus::class, AssignOp\Minus::class,
            AssignOp\Mul::class, AssignOp\Div::class, AssignOp\Concat::class, AssignOp\Mod::class,
            AssignOp\BitwiseAnd::class, AssignOp\BitwiseOr::class, AssignOp\BitwiseXor::class,
            AssignOp\ShiftLeft::class, AssignOp\ShiftRight::class, AssignOp\Pow::class, AssignOp\Coalesce::class
        ];
        foreach ($assignOps as $assignOp) {
            $this->fixupMap[$assignOp] = [
                'var' => self::FIXUP_PREC_LEFT,
                'expr' => self::FIXUP_PREC_RIGHT,
            ];
        }
        $prefixOps = [
            Expr\BitwiseNot::class, Expr\BooleanNot::class, Expr\UnaryPlus::class, Expr\UnaryMinus::class,
            Cast\Int_::class, Cast\Double::class, Cast\String_::class, Cast\Array_::class,
            Cast\Object_::class, Cast\Bool_::class, Cast\Unset_::class, Expr\ErrorSuppress::class,
            Expr\YieldFrom::class, Expr\Print_::class, Expr\Include_::class,
        ];
        foreach ($prefixOps as $prefixOp) {
            $this->fixupMap[$prefixOp] = ['expr' => self::FIXUP_PREC_RIGHT];
        }
    }
    protected function initializeRemovalMap() {
        if ($this->removalMap) return;
        $stripBoth = ['left' => \T_WHITESPACE, 'right' => \T_WHITESPACE];
        $stripLeft = ['left' => \T_WHITESPACE];
        $stripRight = ['right' => \T_WHITESPACE];
        $stripDoubleArrow = ['right' => \T_DOUBLE_ARROW];
        $stripColon = ['left' => ':'];
        $stripEquals = ['left' => '='];
        $this->removalMap = [
            'Expr_ArrayDimFetch->dim' => $stripBoth,
            'Expr_ArrayItem->key' => $stripDoubleArrow,
            'Expr_Closure->returnType' => $stripColon,
            'Expr_Exit->expr' => $stripBoth,
            'Expr_Ternary->if' => $stripBoth,
            'Expr_Yield->key' => $stripDoubleArrow,
            'Expr_Yield->value' => $stripBoth,
            'Param->type' => $stripRight,
            'Param->default' => $stripEquals,
            'Stmt_Break->num' => $stripBoth,
            'Stmt_ClassMethod->returnType' => $stripColon,
            'Stmt_Class->extends' => ['left' => \T_EXTENDS],
            'Expr_PrintableNewAnonClass->extends' => ['left' => \T_EXTENDS],
            'Stmt_Continue->num' => $stripBoth,
            'Stmt_Foreach->keyVar' => $stripDoubleArrow,
            'Stmt_Function->returnType' => $stripColon,
            'Stmt_If->else' => $stripLeft,
            'Stmt_Namespace->name' => $stripLeft,
            'Stmt_Property->type' => $stripRight,
            'Stmt_PropertyProperty->default' => $stripEquals,
            'Stmt_Return->expr' => $stripBoth,
            'Stmt_StaticVar->default' => $stripEquals,
            'Stmt_TraitUseAdaptation_Alias->newName' => $stripLeft,
            'Stmt_TryCatch->finally' => $stripLeft,
        ];
    }
    protected function initializeInsertionMap() {
        if ($this->insertionMap) return;
        $this->insertionMap = [
            'Expr_ArrayDimFetch->dim' => ['[', false, null, null],
            'Expr_ArrayItem->key' => [null, false, null, ' => '],
            'Expr_Closure->returnType' => [')', false, ' : ', null],
            'Expr_Ternary->if' => ['?', false, ' ', ' '],
            'Expr_Yield->key' => [\T_YIELD, false, null, ' => '],
            'Expr_Yield->value' => [\T_YIELD, false, ' ', null],
            'Param->type' => [null, false, null, ' '],
            'Param->default' => [null, false, ' = ', null],
            'Stmt_Break->num' => [\T_BREAK, false, ' ', null],
            'Stmt_ClassMethod->returnType' => [')', false, ' : ', null],
            'Stmt_Class->extends' => [null, false, ' extends ', null],
            'Expr_PrintableNewAnonClass->extends' => [null, ' extends ', null],
            'Stmt_Continue->num' => [\T_CONTINUE, false, ' ', null],
            'Stmt_Foreach->keyVar' => [\T_AS, false, null, ' => '],
            'Stmt_Function->returnType' => [')', false, ' : ', null],
            'Stmt_If->else' => [null, false, ' ', null],
            'Stmt_Namespace->name' => [\T_NAMESPACE, false, ' ', null],
            'Stmt_Property->type' => [\T_VARIABLE, true, null, ' '],
            'Stmt_PropertyProperty->default' => [null, false, ' = ', null],
            'Stmt_Return->expr' => [\T_RETURN, false, ' ', null],
            'Stmt_StaticVar->default' => [null, false, ' = ', null],
            'Stmt_TryCatch->finally' => [null, false, ' ', null],
        ];
    }
    protected function initializeListInsertionMap() {
        if ($this->listInsertionMap) return;
        $this->listInsertionMap = [
            'Stmt_Catch->types' => '|',
            'Stmt_If->elseifs' => ' ',
            'Stmt_TryCatch->catches' => ' ',
            'Expr_Array->items' => ', ',
            'Expr_Closure->params' => ', ',
            'Expr_Closure->uses' => ', ',
            'Expr_FuncCall->args' => ', ',
            'Expr_Isset->vars' => ', ',
            'Expr_List->items' => ', ',
            'Expr_MethodCall->args' => ', ',
            'Expr_New->args' => ', ',
            'Expr_PrintableNewAnonClass->args' => ', ',
            'Expr_StaticCall->args' => ', ',
            'Stmt_ClassConst->consts' => ', ',
            'Stmt_ClassMethod->params' => ', ',
            'Stmt_Class->implements' => ', ',
            'Expr_PrintableNewAnonClass->implements' => ', ',
            'Stmt_Const->consts' => ', ',
            'Stmt_Declare->declares' => ', ',
            'Stmt_Echo->exprs' => ', ',
            'Stmt_For->init' => ', ',
            'Stmt_For->cond' => ', ',
            'Stmt_For->loop' => ', ',
            'Stmt_Function->params' => ', ',
            'Stmt_Global->vars' => ', ',
            'Stmt_GroupUse->uses' => ', ',
            'Stmt_Interface->extends' => ', ',
            'Stmt_Property->props' => ', ',
            'Stmt_StaticVar->vars' => ', ',
            'Stmt_TraitUse->traits' => ', ',
            'Stmt_TraitUseAdaptation_Precedence->insteadof' => ', ',
            'Stmt_Unset->vars' => ', ',
            'Stmt_Use->uses' => ', ',
            'Expr_Closure->stmts' => "\n",
            'Stmt_Case->stmts' => "\n",
            'Stmt_Catch->stmts' => "\n",
            'Stmt_Class->stmts' => "\n",
            'Expr_PrintableNewAnonClass->stmts' => "\n",
            'Stmt_Interface->stmts' => "\n",
            'Stmt_Trait->stmts' => "\n",
            'Stmt_ClassMethod->stmts' => "\n",
            'Stmt_Declare->stmts' => "\n",
            'Stmt_Do->stmts' => "\n",
            'Stmt_ElseIf->stmts' => "\n",
            'Stmt_Else->stmts' => "\n",
            'Stmt_Finally->stmts' => "\n",
            'Stmt_Foreach->stmts' => "\n",
            'Stmt_For->stmts' => "\n",
            'Stmt_Function->stmts' => "\n",
            'Stmt_If->stmts' => "\n",
            'Stmt_Namespace->stmts' => "\n",
            'Stmt_Switch->cases' => "\n",
            'Stmt_TraitUse->adaptations' => "\n",
            'Stmt_TryCatch->stmts' => "\n",
            'Stmt_While->stmts' => "\n",
        ];
    }
    protected function initializeModifierChangeMap() {
        if ($this->modifierChangeMap) return;
        $this->modifierChangeMap = [
            'Stmt_ClassConst->flags' => \T_CONST,
            'Stmt_ClassMethod->flags' => \T_FUNCTION,
            'Stmt_Class->flags' => \T_CLASS,
            'Stmt_Property->flags' => \T_VARIABLE,
        ];
    }
}
