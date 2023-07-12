<?php
class IdenticalBinaryConstraint extends \PHPUnit\Framework\Constraint\Constraint
{
    protected $value;
    public function __construct($value)
    {
        $this->value = $value;
    }
    public function matches($other)
    {
        $aHex = $this->asHexString($this->value);
        $bHex = $this->asHexString($other);
        return $aHex === $bHex;
    }
    public function toString()
    {
        return 'identical binary';
    }
    private function asHexString($binary)
    {
        $hex = '';
        $bytes = unpack('H*', $binary);
        foreach ($bytes as &$byte) {
            $byte = strtoupper($byte);
        }
        return implode('', $bytes);
    }
}
