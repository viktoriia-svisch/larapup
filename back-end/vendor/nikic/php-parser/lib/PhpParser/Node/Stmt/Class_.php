<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Error;
use PhpParser\Node;
class Class_ extends ClassLike
{
    const MODIFIER_PUBLIC    =  1;
    const MODIFIER_PROTECTED =  2;
    const MODIFIER_PRIVATE   =  4;
    const MODIFIER_STATIC    =  8;
    const MODIFIER_ABSTRACT  = 16;
    const MODIFIER_FINAL     = 32;
    const VISIBILITY_MODIFIER_MASK = 7; 
    public $flags;
    public $extends;
    public $implements;
    public function __construct($name, array $subNodes = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->flags = $subNodes['flags'] ?? $subNodes['type'] ?? 0;
        $this->name = \is_string($name) ? new Node\Identifier($name) : $name;
        $this->extends = $subNodes['extends'] ?? null;
        $this->implements = $subNodes['implements'] ?? [];
        $this->stmts = $subNodes['stmts'] ?? [];
    }
    public function getSubNodeNames() : array {
        return ['flags', 'name', 'extends', 'implements', 'stmts'];
    }
    public function isAbstract() : bool {
        return (bool) ($this->flags & self::MODIFIER_ABSTRACT);
    }
    public function isFinal() : bool {
        return (bool) ($this->flags & self::MODIFIER_FINAL);
    }
    public function isAnonymous() : bool {
        return null === $this->name;
    }
    public static function verifyModifier($a, $b) {
        if ($a & self::VISIBILITY_MODIFIER_MASK && $b & self::VISIBILITY_MODIFIER_MASK) {
            throw new Error('Multiple access type modifiers are not allowed');
        }
        if ($a & self::MODIFIER_ABSTRACT && $b & self::MODIFIER_ABSTRACT) {
            throw new Error('Multiple abstract modifiers are not allowed');
        }
        if ($a & self::MODIFIER_STATIC && $b & self::MODIFIER_STATIC) {
            throw new Error('Multiple static modifiers are not allowed');
        }
        if ($a & self::MODIFIER_FINAL && $b & self::MODIFIER_FINAL) {
            throw new Error('Multiple final modifiers are not allowed');
        }
        if ($a & 48 && $b & 48) {
            throw new Error('Cannot use the final modifier on an abstract class member');
        }
    }
    public function getType() : string {
        return 'Stmt_Class';
    }
}
