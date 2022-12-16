<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt\TraitUseAdaptation;
use PhpParser\Node;
class Precedence extends Node\Stmt\TraitUseAdaptation
{
    public $insteadof;
    public function __construct(Node\Name $trait, $method, array $insteadof, array $attributes = []) {
        parent::__construct($attributes);
        $this->trait = $trait;
        $this->method = \is_string($method) ? new Node\Identifier($method) : $method;
        $this->insteadof = $insteadof;
    }
    public function getSubNodeNames() : array {
        return ['trait', 'method', 'insteadof'];
    }
    public function getType() : string {
        return 'Stmt_TraitUseAdaptation_Precedence';
    }
}
