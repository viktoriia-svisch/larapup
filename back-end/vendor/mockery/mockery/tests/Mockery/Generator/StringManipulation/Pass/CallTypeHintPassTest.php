<?php
namespace Mockery\Test\Generator\StringManipulation\Pass;
use Mockery as m;
use Mockery\Generator\StringManipulation\Pass\CallTypeHintPass;
use PHPUnit\Framework\TestCase;
class CallTypeHintPassTest extends TestCase
{
    const CODE = ' public function __call($method, array $args) {}
                   public static function __callStatic($method, array $args) {}
    ';
    public function shouldRemoveCallTypeHintIfRequired()
    {
        $pass = new CallTypeHintPass;
        $config = m::mock("Mockery\Generator\MockConfiguration", array(
            "requiresCallTypeHintRemoval" => true,
        ))->makePartial();
        $code = $pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, '__call($method, $args)') !== false);
    }
    public function shouldRemoveCallStaticTypeHintIfRequired()
    {
        $pass = new CallTypeHintPass;
        $config = m::mock("Mockery\Generator\MockConfiguration", array(
            "requiresCallStaticTypeHintRemoval" => true,
        ))->makePartial();
        $code = $pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, '__callStatic($method, $args)') !== false);
    }
}
