<?php declare(strict_types=1);
namespace SebastianBergmann\ResourceOperations;
use PHPUnit\Framework\TestCase;
final class ResourceOperationsTest extends TestCase
{
    public function testGetFunctions(): void
    {
        $functions = ResourceOperations::getFunctions();
        $this->assertInternalType('array', $functions);
        $this->assertContains('fopen', $functions);
    }
}
