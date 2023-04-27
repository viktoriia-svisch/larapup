<?php
namespace PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\MockObject\Invocation;
use PHPUnit\Framework\MockObject\Stub;
class ReturnValueMap implements Stub
{
    private $valueMap;
    public function __construct(array $valueMap)
    {
        $this->valueMap = $valueMap;
    }
    public function invoke(Invocation $invocation)
    {
        $parameterCount = \count($invocation->getParameters());
        foreach ($this->valueMap as $map) {
            if (!\is_array($map) || $parameterCount !== (\count($map) - 1)) {
                continue;
            }
            $return = \array_pop($map);
            if ($invocation->getParameters() === $map) {
                return $return;
            }
        }
    }
    public function toString(): string
    {
        return 'return value from a map';
    }
}
