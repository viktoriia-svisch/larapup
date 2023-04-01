<?php
namespace SebastianBergmann\CodeUnitReverseLookup;
class Wizard
{
    private $lookupTable = [];
    private $processedClasses = [];
    private $processedFunctions = [];
    public function lookup($filename, $lineNumber)
    {
        if (!isset($this->lookupTable[$filename][$lineNumber])) {
            $this->updateLookupTable();
        }
        if (isset($this->lookupTable[$filename][$lineNumber])) {
            return $this->lookupTable[$filename][$lineNumber];
        } else {
            return $filename . ':' . $lineNumber;
        }
    }
    private function updateLookupTable()
    {
        $this->processClassesAndTraits();
        $this->processFunctions();
    }
    private function processClassesAndTraits()
    {
        foreach (array_merge(get_declared_classes(), get_declared_traits()) as $classOrTrait) {
            if (isset($this->processedClasses[$classOrTrait])) {
                continue;
            }
            $reflector = new \ReflectionClass($classOrTrait);
            foreach ($reflector->getMethods() as $method) {
                $this->processFunctionOrMethod($method);
            }
            $this->processedClasses[$classOrTrait] = true;
        }
    }
    private function processFunctions()
    {
        foreach (get_defined_functions()['user'] as $function) {
            if (isset($this->processedFunctions[$function])) {
                continue;
            }
            $this->processFunctionOrMethod(new \ReflectionFunction($function));
            $this->processedFunctions[$function] = true;
        }
    }
    private function processFunctionOrMethod(\ReflectionFunctionAbstract $functionOrMethod)
    {
        if ($functionOrMethod->isInternal()) {
            return;
        }
        $name = $functionOrMethod->getName();
        if ($functionOrMethod instanceof \ReflectionMethod) {
            $name = $functionOrMethod->getDeclaringClass()->getName() . '::' . $name;
        }
        if (!isset($this->lookupTable[$functionOrMethod->getFileName()])) {
            $this->lookupTable[$functionOrMethod->getFileName()] = [];
        }
        foreach (range($functionOrMethod->getStartLine(), $functionOrMethod->getEndLine()) as $line) {
            $this->lookupTable[$functionOrMethod->getFileName()][$line] = $name;
        }
    }
}
