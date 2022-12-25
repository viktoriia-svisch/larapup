<?php declare(strict_types=1);
namespace PHPUnit\Util;
use PHPUnit\Framework\TestCase;
class XDebugFilterScriptGeneratorTest extends TestCase
{
    public function testReturnsExpectedScript(): void
    {
        $expectedDirectory = \sprintf('%s/', __DIR__);
        $expected          = <<<EOF
<?php declare(strict_types=1);
if (!\\function_exists('xdebug_set_filter')) {
    return;
}
\\xdebug_set_filter(
    \\XDEBUG_FILTER_CODE_COVERAGE,
    \\XDEBUG_PATH_WHITELIST,
    [
        '$expectedDirectory',
        '$expectedDirectory',
        '$expectedDirectory',
        'src/foo.php',
        'src/bar.php'
    ]
);
EOF;
        $directoryPathThatDoesNotExist = \sprintf('%s/path/that/does/not/exist', __DIR__);
        $this->assertDirectoryNotExists($directoryPathThatDoesNotExist);
        $filterConfiguration = [
            'include' => [
                'directory' => [
                    [
                        'path'   => __DIR__,
                        'suffix' => '.php',
                        'prefix' => '',
                    ],
                    [
                        'path'   => \sprintf('%s/', __DIR__),
                        'suffix' => '.php',
                        'prefix' => '',
                    ],
                    [
                        'path'   => \sprintf('%s/./%s', \dirname(__DIR__), \basename(__DIR__)),
                        'suffix' => '.php',
                        'prefix' => '',
                    ],
                    [
                        'path'   => $directoryPathThatDoesNotExist,
                        'suffix' => '.php',
                        'prefix' => '',
                    ],
                ],
                'file' => [
                    'src/foo.php',
                    'src/bar.php',
                ],
            ],
            'exclude' => [
                'directory' => [],
                'file'      => [],
            ],
        ];
        $writer = new XdebugFilterScriptGenerator;
        $actual = $writer->generate($filterConfiguration);
        $this->assertSame($expected, $actual);
    }
}
