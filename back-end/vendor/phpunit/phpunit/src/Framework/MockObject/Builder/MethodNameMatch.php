<?php
namespace PHPUnit\Framework\MockObject\Builder;
interface MethodNameMatch extends ParametersMatch
{
    public function method($name);
}
