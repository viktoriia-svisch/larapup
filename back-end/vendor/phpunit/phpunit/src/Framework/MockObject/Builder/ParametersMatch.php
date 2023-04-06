<?php
namespace PHPUnit\Framework\MockObject\Builder;
use PHPUnit\Framework\MockObject\Matcher\AnyParameters;
interface ParametersMatch extends Match
{
    public function with(...$arguments);
    public function withAnyParameters();
}
