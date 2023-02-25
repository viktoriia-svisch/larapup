<?php
namespace PHPUnit\Util;
final class ConfigurationGenerator
{
    private const TEMPLATE = <<<EOT
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http:
         xsi:noNamespaceSchemaLocation="https:
         bootstrap="{bootstrap_script}"
         forceCoversAnnotation="true"
         beStrictAboutCoversAnnotation="true"
         beStrictAboutOutputDuringTests="true"
         beStrictAboutTodoAnnotatedTests="true"
         verbose="true">
    <testsuites>
        <testsuite name="default">
            <directory suffix="Test.php">{tests_directory}</directory>
        </testsuite>
    </testsuites>
    <filter>
        <whitelist processUncoveredFilesFromWhitelist="true">
            <directory suffix=".php">{src_directory}</directory>
        </whitelist>
    </filter>
</phpunit>
EOT;
    public function generateDefaultConfiguration(string $phpunitVersion, string $bootstrapScript, string $testsDirectory, string $srcDirectory): string
    {
        return \str_replace(
            [
                '{phpunit_version}',
                '{bootstrap_script}',
                '{tests_directory}',
                '{src_directory}',
            ],
            [
                $phpunitVersion,
                $bootstrapScript,
                $testsDirectory,
                $srcDirectory,
            ],
            self::TEMPLATE
        );
    }
}
