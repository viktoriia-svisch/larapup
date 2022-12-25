<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Global_ extends Node\Stmt
{
    public $vars;
    public function __construct(array $vars, array $attributes = []) {
        parent::__construct($attributes);
        $this->vars = $vars;
    }
    public function getSubNodeNames() : array {
        return ['vars'];
    }
    public function getType() : string {
        return 'Stmt_Global';
    }
}
