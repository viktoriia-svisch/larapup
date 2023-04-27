<?php declare(strict_types=1);
namespace PhpParser\Internal;
use PhpParser\Node;
use PhpParser\Node\Expr;
class PrintableNewAnonClassNode extends Expr
{
    public $args;
    public $extends;
    public $implements;
    public $stmts;
    public function __construct(
        array $args, Node\Name $extends = null, array $implements, array $stmts, array $attributes
    ) {
        parent::__construct($attributes);
        $this->args = $args;
        $this->extends = $extends;
        $this->implements = $implements;
        $this->stmts = $stmts;
    }
    public static function fromNewNode(Expr\New_ $newNode) {
        $class = $newNode->class;
        assert($class instanceof Node\Stmt\Class_);
        return new self(
            $newNode->args, $class->extends, $class->implements,
            $class->stmts, $newNode->getAttributes()
        );
    }
    public function getType() : string {
        return 'Expr_PrintableNewAnonClass';
    }
    public function getSubNodeNames() : array {
        return ['args', 'extends', 'implements', 'stmts'];
    }
}
