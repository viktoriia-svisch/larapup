<?php
namespace PHPUnit\Framework\MockObject;
interface Invocation
{
    public function generateReturnValue();
    public function getClassName(): string;
    public function getMethodName(): string;
    public function getParameters(): array;
    public function getReturnType(): string;
    public function isReturnTypeNullable(): bool;
}
