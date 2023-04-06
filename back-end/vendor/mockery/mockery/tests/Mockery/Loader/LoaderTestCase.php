<?php
namespace Mockery\Loader;
use Mockery\Generator\MockConfiguration;
use Mockery\Generator\MockDefinition;
use PHPUnit\Framework\TestCase;
abstract class LoaderTestCase extends TestCase
{
    public function loadLoadsTheCode()
    {
        $className = 'Mock_' . uniqid();
        $config = new MockConfiguration(array(), array(), array(), $className);
        $code = "<?php class $className { } ";
        $definition = new MockDefinition($config, $code);
        $this->getLoader()->load($definition);
        $this->assertTrue(class_exists($className));
    }
    abstract public function getLoader();
}
