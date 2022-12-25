<?php
namespace PHPUnit\Runner;
use ReflectionClass;
interface TestSuiteLoader
{
    public function load(string $suiteClassName, string $suiteClassFile = ''): ReflectionClass;
    public function reload(ReflectionClass $aClass): ReflectionClass;
}
