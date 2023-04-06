<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt\TraitUseAdaptation;
use PhpParser\Node;
class Alias extends Node\Stmt\TraitUseAdaptation
{
    public $newModifier;
    public $newName;
    public function __construct($trait, $method, $newModifier, $newName, array $attributes = []) {
        parent::__construct($attributes);
        $this->trait = $trait;
        $this->method = \is_string($method) ? new Node\Identifier($method) : $method;
        $this->newModifier = $newModifier;
        $this->newName = \is_string($newName) ? new Node\Identifier($newName) : $newName;
    }
    public function getSubNodeNames() : array {
        return ['trait', 'method', 'newModifier', 'newName'];
    }
    public function getType() : string {
        return 'Stmt_TraitUseAdaptation_Alias';
    }
}
