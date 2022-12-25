<?php declare(strict_types=1);
namespace PHPUnit\Util;
final class XdebugFilterScriptGenerator
{
    public function generate(array $filterData): string
    {
        $items = $this->getWhitelistItems($filterData);
        $files = \array_map(
            function ($item) {
                return \sprintf(
                    "        '%s'",
                    $item
                );
            },
            $items
        );
        $files = \implode(",\n", $files);
        return <<<EOF
<?php declare(strict_types=1);
if (!\\function_exists('xdebug_set_filter')) {
    return;
}
\\xdebug_set_filter(
    \\XDEBUG_FILTER_CODE_COVERAGE,
    \\XDEBUG_PATH_WHITELIST,
    [
$files
    ]
);
EOF;
    }
    private function getWhitelistItems(array $filterData): array
    {
        $files = [];
        if (isset($filterData['include']['directory'])) {
            foreach ($filterData['include']['directory'] as $directory) {
                $path = \realpath($directory['path']);
                if (\is_string($path)) {
                    $files[] = \sprintf('%s/', $path);
                }
            }
        }
        if (isset($filterData['include']['directory'])) {
            foreach ($filterData['include']['file'] as $file) {
                $files[] = $file;
            }
        }
        return $files;
    }
}
