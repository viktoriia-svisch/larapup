<?php
declare(strict_types=1);
namespace Mockery\Adapter\Phpunit;
trait MockeryPHPUnitIntegrationAssertPostConditionsForV8
{
    protected function assertPostConditions() : void
    {
        $this->mockeryAssertPostConditions();
    }
}
