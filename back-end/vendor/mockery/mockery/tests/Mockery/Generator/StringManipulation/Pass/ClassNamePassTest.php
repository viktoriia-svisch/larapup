<?php
namespace Mockery\Generator\StringManipulation\Pass;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Generator\MockConfiguration;
class ClassNamePassTest extends MockeryTestCase
{
    const CODE = "namespace Mockery; class Mock {}";
    public function mockeryTestSetUp()
    {
        $this->pass = new ClassNamePass();
    }
    public function shouldRemoveNamespaceDefinition()
    {
        $config = new MockConfiguration(array(), array(), array(), "Dave\Dave");
        $code = $this->pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, 'namespace Mockery;') === false);
    }
    public function shouldReplaceNamespaceIfClassNameIsNamespaced()
    {
        $config = new MockConfiguration(array(), array(), array(), "Dave\Dave");
        $code = $this->pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, 'namespace Mockery;') === false);
        $this->assertTrue(\mb_strpos($code, 'namespace Dave;') !== false);
    }
    public function shouldReplaceClassNameWithSpecifiedName()
    {
        $config = new MockConfiguration(array(), array(), array(), "Dave");
        $code = $this->pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, 'class Dave') !== false);
    }
    public function shouldRemoveLeadingBackslashesFromNamespace()
    {
        $config = new MockConfiguration(array(), array(), array(), "\Dave\Dave");
        $code = $this->pass->apply(static::CODE, $config);
        $this->assertTrue(\mb_strpos($code, 'namespace Dave;') !== false);
    }
}
