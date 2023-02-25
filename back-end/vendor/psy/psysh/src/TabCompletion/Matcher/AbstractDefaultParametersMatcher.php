<?php
namespace Psy\TabCompletion\Matcher;
abstract class AbstractDefaultParametersMatcher extends AbstractContextAwareMatcher
{
    public function getDefaultParameterCompletion(array $reflectionParameters)
    {
        $parametersProcessed = [];
        foreach ($reflectionParameters as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                return [];
            }
            $defaultValue = $this->valueToShortString($parameter->getDefaultValue());
            $parametersProcessed[] = "\${$parameter->getName()} = $defaultValue";
        }
        if (empty($parametersProcessed)) {
            return [];
        }
        return [\implode(', ', $parametersProcessed) . ')'];
    }
    private function valueToShortString($value)
    {
        if (!\is_array($value)) {
            return \json_encode($value);
        }
        $chunks = [];
        $chunksSequential = [];
        $allSequential = true;
        foreach ($value as $key => $item) {
            $allSequential = $allSequential && \is_numeric($key) && $key === \count($chunksSequential);
            $keyString  = $this->valueToShortString($key);
            $itemString = $this->valueToShortString($item);
            $chunks[] = "{$keyString} => {$itemString}";
            $chunksSequential[] = $itemString;
        }
        $chunksToImplode = $allSequential ? $chunksSequential : $chunks;
        return '[' . \implode(', ', $chunksToImplode) . ']';
    }
}
