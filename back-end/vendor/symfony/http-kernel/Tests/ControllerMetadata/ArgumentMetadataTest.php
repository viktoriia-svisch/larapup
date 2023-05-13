<?php
namespace Symfony\Component\HttpKernel\Tests\ControllerMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
class ArgumentMetadataTest extends TestCase
{
    public function testWithBcLayerWithDefault()
    {
        $argument = new ArgumentMetadata('foo', 'string', false, true, 'default value');
        $this->assertFalse($argument->isNullable());
    }
    public function testDefaultValueAvailable()
    {
        $argument = new ArgumentMetadata('foo', 'string', false, true, 'default value', true);
        $this->assertTrue($argument->isNullable());
        $this->assertTrue($argument->hasDefaultValue());
        $this->assertSame('default value', $argument->getDefaultValue());
    }
    public function testDefaultValueUnavailable()
    {
        $argument = new ArgumentMetadata('foo', 'string', false, false, null, false);
        $this->assertFalse($argument->isNullable());
        $this->assertFalse($argument->hasDefaultValue());
        $argument->getDefaultValue();
    }
}
