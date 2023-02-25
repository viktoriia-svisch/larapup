<?php
namespace Mockery\Test\Generator\StringManipulation\Pass;
use Mockery as m;
use Mockery\Generator\MockConfigurationBuilder;
use Mockery\Generator\StringManipulation\Pass\InstanceMockPass;
use PHPUnit\Framework\TestCase;
class InstanceMockPassTest extends TestCase
{
    public function shouldAppendConstructorAndPropertyForInstanceMock()
    {
        $builder = new MockConfigurationBuilder;
        $builder->setInstanceMock(true);
        $config = $builder->getMockConfiguration();
        $pass = new InstanceMockPass;
        $code = $pass->apply('class Dave { }', $config);
        $this->assertTrue(\mb_strpos($code, 'public function __construct') !== false);
        $this->assertTrue(\mb_strpos($code, 'protected $_mockery_ignoreVerification') !== false);
        $this->assertTrue(\mb_strpos($code, 'this->_mockery_constructorCalled(func_get_args());') !== false);
    }
}
