<?php
namespace PHPUnit\Framework\MockObject\Builder;
use PHPUnit\Framework\MockObject\Stub as BaseStub;
interface Stub extends Identity
{
    public function will(BaseStub $stub);
}
