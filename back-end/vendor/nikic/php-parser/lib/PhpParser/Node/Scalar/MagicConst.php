<?php declare(strict_types=1);
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Scalar;
abstract class MagicConst extends Scalar
{
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }
    public function getSubNodeNames() : array {
        return [];
    }
    abstract public function getName() : string;
}
