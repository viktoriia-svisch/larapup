<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
class Const_ extends Node\Stmt
{
    public $consts;
    public function __construct(array $consts, array $attributes = []) {
        parent::__construct($attributes);
        $this->consts = $consts;
    }
    public function getSubNodeNames() : array {
        return ['consts'];
    }
    public function getType() : string {
        return 'Stmt_Const';
    }
}
