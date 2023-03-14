<?php declare(strict_types=1);
namespace PhpParser\Node\Scalar;
use PhpParser\Node\Scalar;
class DNumber extends Scalar
{
    public $value;
    public function __construct(float $value, array $attributes = []) {
        parent::__construct($attributes);
        $this->value = $value;
    }
    public function getSubNodeNames() : array {
        return ['value'];
    }
    public static function parse(string $str) : float {
        if (false !== strpbrk($str, '.eE')) {
            return (float) $str;
        }
        if ('0' === $str[0]) {
            if ('x' === $str[1] || 'X' === $str[1]) {
                return hexdec($str);
            }
            if ('b' === $str[1] || 'B' === $str[1]) {
                return bindec($str);
            }
            return octdec(substr($str, 0, strcspn($str, '89')));
        }
        return (float) $str;
    }
    public function getType() : string {
        return 'Scalar_DNumber';
    }
}
