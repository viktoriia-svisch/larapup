<?php
namespace PHPUnit\Util;
use PHPUnit\Framework\TestCase;
class ConfigurationGeneratorTest extends TestCase
{
    public function testGeneratesConfigurationCorrectly(): void
    {
        $generator = new ConfigurationGenerator;
        $this->assertEquals(
            '<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http:
         xsi:noNamespaceSchemaLocation="https:
         bootstrap="vendor/autoload.php"
         forceCoversAnnotation="true"
         beStrictAboutCoversAnnotation="true"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTodoAnnotatedTests="true"
         verbose="true">
    <testsuites>
        <testsuite name="default">
            <directory suffix="Test.php">tests</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">src</directory>
        </whitelist>
    </filter>
</phpunit>
',
            $generator->generateDefaultConfiguration(
                'X.Y.Z',
                'vendor/autoload.php',
                'tests',
                'src'
            )
        );
    }
}
