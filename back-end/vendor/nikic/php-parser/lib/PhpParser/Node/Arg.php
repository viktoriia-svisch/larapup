<?php declare(strict_types=1);
namespace PhpParser\Node;
use PhpParser\NodeAbstract;
class Arg extends NodeAbstract
{
    public $value;
    public $byRef;
    public $unpack;
    public function __construct(Expr $value, bool $byRef = false, bool $unpack = false, array $attributes = []) {
        parent::__construct($attributes);
        $this->value = $value;
        $this->byRef = $byRef;
        $this->unpack = $unpack;
    }
    public function getSubNodeNames() : array {
        return ['value', 'byRef', 'unpack'];
    }
    public function getType() : string {
        return 'Arg';
    }
}
