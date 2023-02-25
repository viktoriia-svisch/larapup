<?php
namespace Psy\Test\Reflection;
use Psy\Reflection\ReflectionConstant;
class ReflectionConstantBCTest extends \PHPUnit\Framework\TestCase
{
    const CONSTANT_ONE = 'one';
    public function testConstruction()
    {
        $refl = new ReflectionConstant($this, 'CONSTANT_ONE');
        $this->assertInstanceOf('Psy\Reflection\ReflectionConstant', $refl);
        $this->assertInstanceOf('Psy\Reflection\ReflectionClassConstant', $refl);
    }
}
