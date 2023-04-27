<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class HaltCompiler extends Stmt
{
    public $remaining;
    public function __construct(string $remaining, array $attributes = []) {
        parent::__construct($attributes);
        $this->remaining = $remaining;
    }
    public function getSubNodeNames() : array {
        return ['remaining'];
    }
    public function getType() : string {
        return 'Stmt_HaltCompiler';
    }
}
