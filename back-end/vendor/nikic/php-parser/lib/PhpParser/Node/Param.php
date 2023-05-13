<?php declare(strict_types=1);
namespace PhpParser\Node;
use PhpParser\NodeAbstract;
class Param extends NodeAbstract
{
    public $type;
    public $byRef;
    public $variadic;
    public $var;
    public $default;
    public function __construct(
        $var, Expr $default = null, $type = null,
        bool $byRef = false, bool $variadic = false, array $attributes = []
    ) {
        parent::__construct($attributes);
        $this->type = \is_string($type) ? new Identifier($type) : $type;
        $this->byRef = $byRef;
        $this->variadic = $variadic;
        $this->var = $var;
        $this->default = $default;
    }
    public function getSubNodeNames() : array {
        return ['type', 'byRef', 'variadic', 'var', 'default'];
    }
    public function getType() : string {
        return 'Param';
    }
}
