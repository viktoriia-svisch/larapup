<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Node\FunctionLike;
class Function_ extends Node\Stmt implements FunctionLike
{
    public $byRef;
    public $name;
    public $params;
    public $returnType;
    public $stmts;
    public function __construct($name, array $subNodes = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->byRef = $subNodes['byRef'] ?? false;
        $this->name = \is_string($name) ? new Node\Identifier($name) : $name;
        $this->params = $subNodes['params'] ?? [];
        $returnType = $subNodes['returnType'] ?? null;
        $this->returnType = \is_string($returnType) ? new Node\Identifier($returnType) : $returnType;
        $this->stmts = $subNodes['stmts'] ?? [];
    }
    public function getSubNodeNames() : array {
        return ['byRef', 'name', 'params', 'returnType', 'stmts'];
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
        return 'Stmt_Function';
    }
}
