<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node\Stmt;
class Static_ extends Stmt
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
        return 'Stmt_Static';
    }
}
