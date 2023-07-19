<?php declare(strict_types=1);
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Stmt;
class Method extends FunctionLike
{
    protected $name;
    protected $flags = 0;
    protected $stmts = [];
    public function __construct(string $name) {
        $this->name = $name;
    }
    public function makePublic() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_PUBLIC);
        return $this;
    }
    public function makeProtected() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_PROTECTED);
        return $this;
    }
    public function makePrivate() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_PRIVATE);
        return $this;
    }
    public function makeStatic() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_STATIC);
        return $this;
    }
    public function makeAbstract() {
        if (!empty($this->stmts)) {
            throw new \LogicException('Cannot make method with statements abstract');
        }
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_ABSTRACT);
        $this->stmts = null; 
        return $this;
    }
    public function makeFinal() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_FINAL);
        return $this;
    }
    public function addStmt($stmt) {
        if (null === $this->stmts) {
            throw new \LogicException('Cannot add statements to an abstract method');
        }
        $this->stmts[] = BuilderHelpers::normalizeStmt($stmt);
        return $this;
    }
    public function getNode() : Node {
        return new Stmt\ClassMethod($this->name, [
            'flags'      => $this->flags,
            'byRef'      => $this->returnByRef,
            'params'     => $this->params,
            'returnType' => $this->returnType,
            'stmts'      => $this->stmts,
        ], $this->attributes);
    }
}