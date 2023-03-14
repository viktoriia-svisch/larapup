<?php
namespace Mockery\Test\Generator\StringManipulation\Pass;
use Mockery as m;
use Mockery\Generator\MockConfiguration;
use Mockery\Generator\StringManipulation\Pass\InterfacePass;
use PHPUnit\Framework\TestCase;
class InterfacePassTest extends TestCase
{
    const CODE = "class Mock implements MockInterface";
    public function shouldNotAlterCodeIfNoTargetInterfaces()
    {
        $pass = new InterfacePass;
        $config = m::mock("Mockery\Generator\MockConfiguration", array(
            "getTargetInterfaces" => array(),
        ));
        $code = $pass->apply(static::CODE, $config);
        $this->assertEquals(static::CODE, $code);
    }
    public function shouldAddAnyInterfaceNamesToImplementsDefinition()
    {
        $pass = new InterfacePass;
        $config = m::mock("Mockery\Generator\MockConfiguration", array(
            "getTargetInterfaces" => array(
                m::mock(array("getName" => "Dave\Dave")),
                m::mock(array("getName" => "Paddy\Paddy")),
            ),
        ));
        $code = $pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, "implements MockInterface, \Dave\Dave, \Paddy\Paddy") !== false);
    }
}
