<?php
namespace SebastianBergmann\CodeCoverage\Driver;
interface Driver
{
    public const LINE_EXECUTED = 1;
    public const LINE_NOT_EXECUTED = -1;
    public const LINE_NOT_EXECUTABLE = -2;
    public function start(bool $determineUnusedAndDead = true): void;
    public function stop(): array;
}
