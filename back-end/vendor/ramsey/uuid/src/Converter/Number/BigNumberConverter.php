<?php
namespace Ramsey\Uuid\Converter\Number;
use Moontoast\Math\BigNumber;
use Ramsey\Uuid\Converter\NumberConverterInterface;
class BigNumberConverter implements NumberConverterInterface
{
    public function fromHex($hex)
    {
        $number = BigNumber::convertToBase10($hex, 16);
        return new BigNumber($number);
    }
    public function toHex($integer)
    {
        if (!$integer instanceof BigNumber) {
            $integer = new BigNumber($integer);
        }
        return BigNumber::convertFromBase10($integer, 16);
    }
}
