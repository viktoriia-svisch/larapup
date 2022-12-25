<?php declare(strict_types=1);
namespace PhpParser\Node\Expr;
use PhpParser\Node\Expr;
class ShellExec extends Expr
{
    public $parts;
    public function __construct(array $parts, array $attributes = []) {
        parent::__construct($attributes);
        $this->parts = $parts;
    }
    public function getSubNodeNames() : array {
        return ['parts'];
    }
    public function getType() : string {
        return 'Expr_ShellExec';
    }
}
