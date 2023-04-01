<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\FunctionLike;
class Closure extends Expr implements FunctionLike
{
    public $static;
    public $byRef;
    public $params;
    public $uses;
    public $returnType;
    public $stmts;
    public function __construct(array $subNodes = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->static = $subNodes['static'] ?? false;
        $this->byRef = $subNodes['byRef'] ?? false;
        $this->params = $subNodes['params'] ?? [];
        $this->uses = $subNodes['uses'] ?? [];
        $returnType = $subNodes['returnType'] ?? null;
        $this->returnType = \is_string($returnType) ? new Node\Identifier($returnType) : $returnType;
        $this->stmts = $subNodes['stmts'] ?? [];
    }
    public function getSubNodeNames() : array {
        return ['static', 'byRef', 'params', 'uses', 'returnType', 'stmts'];
    }
    public function returnsByRef() : bool {
        return $this->byRef;
    }
    public function getParams() : array {
        return $this->params;
    }
    public function getReturnType() {
        return $this->returnType;
    }
    public function getStmts() : array {
        return $this->stmts;
    }
    public function getType() : string {
        return 'Expr_Closure';
    }
}
