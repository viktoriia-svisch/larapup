<?php declare(strict_types=1);
namespace PhpParser\Node;
use PhpParser\NodeAbstract;
class NullableType extends NodeAbstract
{
    public $type;
    public function __construct($type, array $attributes = []) {
        parent::__construct($attributes);
        $this->type = \is_string($type) ? new Identifier($type) : $type;
    }
    public function getSubNodeNames() : array {
        return ['type'];
    }
    public function getType() : string {
        return 'NullableType';
    }
}
