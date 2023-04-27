<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Generator\MockConfiguration;
class ClassPassTest extends MockeryTestCase
{
    const CODE = "class Mock implements MockInterface {}";
    public function mockeryTestSetUp()
    {
        $this->pass = new ClassPass();
    }
    public function shouldDeclareUnknownClass()
    {
        $config = new MockConfiguration(array("Testing\TestClass"), array(), array(), "Dave\Dave");
        $code = $this->pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, 'class Mock extends \Testing\TestClass implements MockInterface') !== false);
    }
    public function shouldNotExtendHHVMClass()
    {
        $config = new MockConfiguration(array("\HH\\this"), array(), array(), "Dave\Dave");
        $code = $this->pass->apply(static::CODE, $config);
        if (\defined('HHVM_VERSION')) {
            $this->assertNotContains('extends \HH\this', $code);
        } else {
            $this->assertSame('class Mock extends \HH\this implements MockInterface {}', $code);
        }
    }
}
