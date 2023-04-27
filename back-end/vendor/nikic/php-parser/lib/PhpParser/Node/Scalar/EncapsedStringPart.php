<?php declare(strict_types=1);
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Scalar;
class EncapsedStringPart extends Scalar
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
        return 'Scalar_EncapsedStringPart';
    }
}
