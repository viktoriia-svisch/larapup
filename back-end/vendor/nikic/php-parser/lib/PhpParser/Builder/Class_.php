<?php declare(strict_types=1);
namespace PhpParser\Builder;
use PhpParser;
use PhpParser\BuilderHelpers;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
class Class_ extends Declaration
{
    protected $name;
    protected $extends = null;
    protected $implements = [];
    protected $flags = 0;
    protected $uses = [];
    protected $constants = [];
    protected $properties = [];
    protected $methods = [];
    public function __construct(string $name) {
        $this->name = $name;
    }
    public function extend($class) {
        $this->extends = BuilderHelpers::normalizeName($class);
        return $this;
    }
    public function implement(...$interfaces) {
        foreach ($interfaces as $interface) {
            $this->implements[] = BuilderHelpers::normalizeName($interface);
        }
        return $this;
    }
    public function makeAbstract() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_ABSTRACT);
        return $this;
    }
    public function makeFinal() {
        $this->flags = BuilderHelpers::addModifier($this->flags, Stmt\Class_::MODIFIER_FINAL);
        return $this;
    }
    public function addStmt($stmt) {
        $stmt = BuilderHelpers::normalizeNode($stmt);
        $targets = [
            Stmt\TraitUse::class    => &$this->uses,
            Stmt\ClassConst::class  => &$this->constants,
            Stmt\Property::class    => &$this->properties,
            Stmt\ClassMethod::class => &$this->methods,
        ];
        $class = \get_class($stmt);
        if (!isset($targets[$class])) {
            throw new \LogicException(sprintf('Unexpected node of type "%s"', $stmt->getType()));
        }
        $targets[$class][] = $stmt;
        return $this;
    }
    public function getNode() : PhpParser\Node {
        return new Stmt\Class_($this->name, [
            'flags' => $this->flags,
            'extends' => $this->extends,
            'implements' => $this->implements,
            'stmts' => array_merge($this->uses, $this->constants, $this->properties, $this->methods),
        ], $this->attributes);
    }
}
