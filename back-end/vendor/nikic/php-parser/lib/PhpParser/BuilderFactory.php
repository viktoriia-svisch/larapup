<?php declare(strict_types=1);
namespace PhpParser;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Use_;
class BuilderFactory
{
    public function namespace($name) : Builder\Namespace_ {
        return new Builder\Namespace_($name);
    }
    public function class(string $name) : Builder\Class_ {
        return new Builder\Class_($name);
    }
    public function interface(string $name) : Builder\Interface_ {
        return new Builder\Interface_($name);
    }
    public function trait(string $name) : Builder\Trait_ {
        return new Builder\Trait_($name);
    }
    public function useTrait(...$traits) : Builder\TraitUse {
        return new Builder\TraitUse(...$traits);
    }
    public function traitUseAdaptation($trait, $method = null) : Builder\TraitUseAdaptation {
        if ($method === null) {
            $method = $trait;
            $trait = null;
        }
        return new Builder\TraitUseAdaptation($trait, $method);
    }
    public function method(string $name) : Builder\Method {
        return new Builder\Method($name);
    }
    public function param(string $name) : Builder\Param {
        return new Builder\Param($name);
    }
    public function property(string $name) : Builder\Property {
        return new Builder\Property($name);
    }
    public function function(string $name) : Builder\Function_ {
        return new Builder\Function_($name);
    }
    public function use($name) : Builder\Use_ {
        return new Builder\Use_($name, Use_::TYPE_NORMAL);
    }
    public function useFunction($name) : Builder\Use_ {
        return new Builder\Use_($name, Use_::TYPE_FUNCTION);
    }
    public function useConst($name) : Builder\Use_ {
        return new Builder\Use_($name, Use_::TYPE_CONSTANT);
    }
    public function val($value) : Expr {
        return BuilderHelpers::normalizeValue($value);
    }
    public function var($name) : Expr\Variable {
        if (!\is_string($name) && !$name instanceof Expr) {
            throw new \LogicException('Variable name must be string or Expr');
        }
        return new Expr\Variable($name);
    }
    public function args(array $args) : array {
        $normalizedArgs = [];
        foreach ($args as $arg) {
            if ($arg instanceof Arg) {
                $normalizedArgs[] = $arg;
            } else {
                $normalizedArgs[] = new Arg(BuilderHelpers::normalizeValue($arg));
            }
        }
        return $normalizedArgs;
    }
    public function funcCall($name, array $args = []) : Expr\FuncCall {
        return new Expr\FuncCall(
            BuilderHelpers::normalizeNameOrExpr($name),
            $this->args($args)
        );
    }
    public function methodCall(Expr $var, $name, array $args = []) : Expr\MethodCall {
        return new Expr\MethodCall(
            $var,
            BuilderHelpers::normalizeIdentifierOrExpr($name),
            $this->args($args)
        );
    }
    public function staticCall($class, $name, array $args = []) : Expr\StaticCall {
        return new Expr\StaticCall(
            BuilderHelpers::normalizeNameOrExpr($class),
            BuilderHelpers::normalizeIdentifierOrExpr($name),
            $this->args($args)
        );
    }
    public function new($class, array $args = []) : Expr\New_ {
        return new Expr\New_(
            BuilderHelpers::normalizeNameOrExpr($class),
            $this->args($args)
        );
    }
    public function constFetch($name) : Expr\ConstFetch {
        return new Expr\ConstFetch(BuilderHelpers::normalizeName($name));
    }
    public function propertyFetch(Expr $var, $name) : Expr\PropertyFetch {
        return new Expr\PropertyFetch($var, BuilderHelpers::normalizeIdentifierOrExpr($name));
    }
    public function classConstFetch($class, $name): Expr\ClassConstFetch {
        return new Expr\ClassConstFetch(
            BuilderHelpers::normalizeNameOrExpr($class),
            BuilderHelpers::normalizeIdentifier($name)
        );
    }
    public function concat(...$exprs) : Concat {
        $numExprs = count($exprs);
        if ($numExprs < 2) {
            throw new \LogicException('Expected at least two expressions');
        }
        $lastConcat = $this->normalizeStringExpr($exprs[0]);
        for ($i = 1; $i < $numExprs; $i++) {
            $lastConcat = new Concat($lastConcat, $this->normalizeStringExpr($exprs[$i]));
        }
        return $lastConcat;
    }
    private function normalizeStringExpr($expr) : Expr {
        if ($expr instanceof Expr) {
            return $expr;
        }
        if (\is_string($expr)) {
            return new String_($expr);
        }
        throw new \LogicException('Expected string or Expr');
    }
}
