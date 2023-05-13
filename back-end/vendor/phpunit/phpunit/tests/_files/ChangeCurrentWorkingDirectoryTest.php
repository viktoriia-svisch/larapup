<?php
use PHPUnit\Framework\TestCase;
class ChangeCurrentWorkingDirectoryTest extends TestCase
{
    public function testSomethingThatChangesTheCwd(): void
    {
        \chdir('../');
        $this->assertTrue(true);
    }
}
