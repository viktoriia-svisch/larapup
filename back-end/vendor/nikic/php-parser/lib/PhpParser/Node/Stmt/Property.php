<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
class Property extends Node\Stmt
{
    public $flags;
    public $props;
    public $type;
    public function __construct(int $flags, array $props, array $attributes = [], $type = null) {
        parent::__construct($attributes);
        $this->flags = $flags;
        $this->props = $props;
        $this->type = \is_string($type) ? new Identifier($type) : $type;
    }
    public function getSubNodeNames() : array {
        return ['flags', 'type', 'props'];
    }
    public function isPublic() : bool {
        return ($this->flags & Class_::MODIFIER_PUBLIC) !== 0
            || ($this->flags & Class_::VISIBILITY_MODIFIER_MASK) === 0;
    }
    public function isProtected() : bool {
        return (bool) ($this->flags & Class_::MODIFIER_PROTECTED);
    }
    public function isPrivate() : bool {
        return (bool) ($this->flags & Class_::MODIFIER_PRIVATE);
    }
    public function isStatic() : bool {
        return (bool) ($this->flags & Class_::MODIFIER_STATIC);
    }
    public function getType() : string {
        return 'Stmt_Property';
    }
}
