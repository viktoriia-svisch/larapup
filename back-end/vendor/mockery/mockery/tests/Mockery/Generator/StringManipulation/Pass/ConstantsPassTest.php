<?php
namespace Mockery\Test\Generator\StringManipulation\Pass;
use Mockery as m;
use Mockery\Generator\MockConfiguration;
use Mockery\Generator\StringManipulation\Pass\ConstantsPass;
use PHPUnit\Framework\TestCase;
class ConstantsPassTest extends TestCase
{
    const CODE = 'class Foo {}';
    public function shouldAddConstants()
    {
        $pass = new ConstantsPass;
        $config = new MockConfiguration(
            array(),
            array(),
            array(),
            "ClassWithConstants",
            false,
            array(),
            false,
            ['ClassWithConstants' => ['FOO' => 'test']]
        );
        $code = $pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, "const FOO = 'test'") !== false);
    }
}
