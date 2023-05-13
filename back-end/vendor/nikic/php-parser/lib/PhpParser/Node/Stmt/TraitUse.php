<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class TraitUse extends Node\Stmt
{
    public $traits;
    public $adaptations;
    public function __construct(array $traits, array $adaptations = [], array $attributes = []) {
        parent::__construct($attributes);
        $this->traits = $traits;
        $this->adaptations = $adaptations;
    }
    public function getSubNodeNames() : array {
        return ['traits', 'adaptations'];
    }
    public function getType() : string {
        return 'Stmt_TraitUse';
    }
}
