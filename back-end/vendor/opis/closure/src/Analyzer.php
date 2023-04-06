<?php
namespace Opis\Closure;
use Closure;
use SuperClosure\Analyzer\ClosureAnalyzer;
class Analyzer extends ClosureAnalyzer
{
    public function analyze(Closure $closure)
    {
        $reflection = new ReflectionClosure($closure);
        $scope = $reflection->getClosureScopeClass();
        $data = [
            'reflection' => $reflection,
            'code'       => $reflection->getCode(),
            'hasThis'    => $reflection->isBindingRequired(),
            'context'    => $reflection->getUseVariables(),
            'hasRefs'    => false,
            'binding'    => $reflection->getClosureThis(),
            'scope'      => $scope ? $scope->getName() : null,
            'isStatic'   => $reflection->isStatic(),
        ];
        return $data;
    }
    protected function determineCode(array &$data)
    {
        return null;
    }
    protected function determineContext(array &$data)
    {
        return null;
    }
}
