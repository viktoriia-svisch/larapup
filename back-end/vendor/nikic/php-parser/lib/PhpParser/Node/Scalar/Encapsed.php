<?php declare(strict_types=1);
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar;
class Encapsed extends Scalar
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
        return 'Scalar_Encapsed';
    }
}
