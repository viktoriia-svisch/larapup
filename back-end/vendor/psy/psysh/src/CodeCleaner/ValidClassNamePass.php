<?php
namespace Psy\CodeCleaner;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Do_;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Switch_;
use PhpParser\Node\Stmt\Trait_;
use PhpParser\Node\Stmt\While_;
use Psy\Exception\FatalErrorException;
class ValidClassNamePass extends NamespaceAwarePass
{
    const CLASS_TYPE     = 'class';
    const INTERFACE_TYPE = 'interface';
    const TRAIT_TYPE     = 'trait';
    private $conditionalScopes = 0;
    private $atLeastPhp55;
    public function __construct()
    {
        $this->atLeastPhp55 = \version_compare(PHP_VERSION, '5.5', '>=');
    }
    public function enterNode(Node $node)
    {
        parent::enterNode($node);
        if (self::isConditional($node)) {
            $this->conditionalScopes++;
        } else {
            if ($this->conditionalScopes === 0) {
                if ($node instanceof Class_) {
                    $this->validateClassStatement($node);
                } elseif ($node instanceof Interface_) {
                    $this->validateInterfaceStatement($node);
                } elseif ($node instanceof Trait_) {
                    $this->validateTraitStatement($node);
                }
            }
        }
    }
    public function leaveNode(Node $node)
    {
        if (self::isConditional($node)) {
            $this->conditionalScopes--;
        } elseif ($node instanceof New_) {
            $this->validateNewExpression($node);
        } elseif ($node instanceof ClassConstFetch) {
            $this->validateClassConstFetchExpression($node);
        } elseif ($node instanceof StaticCall) {
            $this->validateStaticCallExpression($node);
        }
    }
    private static function isConditional(Node $node)
    {
        return $node instanceof If_ ||
            $node instanceof While_ ||
            $node instanceof Do_ ||
            $node instanceof Switch_;
    }
    protected function validateClassStatement(Class_ $stmt)
    {
        $this->ensureCanDefine($stmt, self::CLASS_TYPE);
        if (isset($stmt->extends)) {
            $this->ensureClassExists($this->getFullyQualifiedName($stmt->extends), $stmt);
        }
        $this->ensureInterfacesExist($stmt->implements, $stmt);
    }
    protected function validateInterfaceStatement(Interface_ $stmt)
    {
        $this->ensureCanDefine($stmt, self::INTERFACE_TYPE);
        $this->ensureInterfacesExist($stmt->extends, $stmt);
    }
    protected function validateTraitStatement(Trait_ $stmt)
    {
        $this->ensureCanDefine($stmt, self::TRAIT_TYPE);
    }
    protected function validateNewExpression(New_ $stmt)
    {
        if (!$stmt->class instanceof Expr && !$stmt->class instanceof Class_) {
            $this->ensureClassExists($this->getFullyQualifiedName($stmt->class), $stmt);
        }
    }
    protected function validateClassConstFetchExpression(ClassConstFetch $stmt)
    {
        if (\strtolower($stmt->name) === 'class' && $this->atLeastPhp55) {
            return;
        }
        if (!$stmt->class instanceof Expr) {
            $this->ensureClassOrInterfaceExists($this->getFullyQualifiedName($stmt->class), $stmt);
        }
    }
    protected function validateStaticCallExpression(StaticCall $stmt)
    {
        if (!$stmt->class instanceof Expr) {
            $this->ensureMethodExists($this->getFullyQualifiedName($stmt->class), $stmt->name, $stmt);
        }
    }
    protected function ensureCanDefine(Stmt $stmt, $scopeType = self::CLASS_TYPE)
    {
        $name = $this->getFullyQualifiedName($stmt->name);
        $errorType = null;
        if ($this->classExists($name)) {
            $errorType = self::CLASS_TYPE;
        } elseif ($this->interfaceExists($name)) {
            $errorType = self::INTERFACE_TYPE;
        } elseif ($this->traitExists($name)) {
            $errorType = self::TRAIT_TYPE;
        }
        if ($errorType !== null) {
            throw $this->createError(\sprintf('%s named %s already exists', \ucfirst($errorType), $name), $stmt);
        }
        $this->currentScope[\strtolower($name)] = $scopeType;
    }
    protected function ensureClassExists($name, $stmt)
    {
        if (!$this->classExists($name)) {
            throw $this->createError(\sprintf('Class \'%s\' not found', $name), $stmt);
        }
    }
    protected function ensureClassOrInterfaceExists($name, $stmt)
    {
        if (!$this->classExists($name) && !$this->interfaceExists($name)) {
            throw $this->createError(\sprintf('Class \'%s\' not found', $name), $stmt);
        }
    }
    protected function ensureClassOrTraitExists($name, $stmt)
    {
        if (!$this->classExists($name) && !$this->traitExists($name)) {
            throw $this->createError(\sprintf('Class \'%s\' not found', $name), $stmt);
        }
    }
    protected function ensureMethodExists($class, $name, $stmt)
    {
        $this->ensureClassOrTraitExists($class, $stmt);
        if (\in_array(\strtolower($class), ['self', 'parent', 'static'])) {
            return;
        }
        if ($this->findInScope($class) === self::CLASS_TYPE) {
            return;
        }
        if ($name instanceof Expr) {
            return;
        }
        if (!\method_exists($class, $name) && !\method_exists($class, '__callStatic')) {
            throw $this->createError(\sprintf('Call to undefined method %s::%s()', $class, $name), $stmt);
        }
    }
    protected function ensureInterfacesExist($interfaces, $stmt)
    {
        foreach ($interfaces as $interface) {
            $name = $this->getFullyQualifiedName($interface);
            if (!$this->interfaceExists($name)) {
                throw $this->createError(\sprintf('Interface \'%s\' not found', $name), $stmt);
            }
        }
    }
    protected function getScopeType(Stmt $stmt)
    {
        if ($stmt instanceof Class_) {
            return self::CLASS_TYPE;
        } elseif ($stmt instanceof Interface_) {
            return self::INTERFACE_TYPE;
        } elseif ($stmt instanceof Trait_) {
            return self::TRAIT_TYPE;
        }
    }
    protected function classExists($name)
    {
        if (\in_array(\strtolower($name), ['self', 'static', 'parent'])) {
            return true;
        }
        return \class_exists($name) || $this->findInScope($name) === self::CLASS_TYPE;
    }
    protected function interfaceExists($name)
    {
        return \interface_exists($name) || $this->findInScope($name) === self::INTERFACE_TYPE;
    }
    protected function traitExists($name)
    {
        return \trait_exists($name) || $this->findInScope($name) === self::TRAIT_TYPE;
    }
    protected function findInScope($name)
    {
        $name = \strtolower($name);
        if (isset($this->currentScope[$name])) {
            return $this->currentScope[$name];
        }
    }
    protected function createError($msg, $stmt)
    {
        return new FatalErrorException($msg, 0, E_ERROR, null, $stmt->getLine());
    }
}
