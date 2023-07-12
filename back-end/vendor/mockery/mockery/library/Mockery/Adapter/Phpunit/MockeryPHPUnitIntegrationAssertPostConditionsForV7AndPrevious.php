<?php
namespace Mockery\Adapter\Phpunit;
trait MockeryPHPUnitIntegrationAssertPostConditionsForV7AndPrevious
{
    protected function assertPostConditions()
    {
        $this->mockeryAssertPostConditions();
    }
}
