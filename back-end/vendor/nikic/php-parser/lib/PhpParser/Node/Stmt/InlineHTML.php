<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class InlineHTML extends Stmt
{
    public $value;
    public function __construct(string $value, array $attributes = []) {
        parent::__construct($attributes);
        $this->value = $value;
    }
    public function getSubNodeNames() : array {
        return ['value'];
    }
    public function getType() : string {
        return 'Stmt_InlineHTML';
    }
}
