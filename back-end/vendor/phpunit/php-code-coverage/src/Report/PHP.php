<?php
namespace SebastianBergmann\CodeCoverage\Report;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\RuntimeException;
final class PHP
{
    public function process(CodeCoverage $coverage, ?string $target = null): string
    {
        $filter = $coverage->filter();
        $buffer = \sprintf(
            '<?php
$coverage = new SebastianBergmann\CodeCoverage\CodeCoverage;
$coverage->setData(%s);
$coverage->setTests(%s);
$filter = $coverage->filter();
$filter->setWhitelistedFiles(%s);
return $coverage;',
            \var_export($coverage->getData(true), 1),
            \var_export($coverage->getTests(), 1),
            \var_export($filter->getWhitelistedFiles(), 1)
        );
        if ($target !== null) {
            if (!$this->createDirectory(\dirname($target))) {
                throw new \RuntimeException(\sprintf('Directory "%s" was not created', \dirname($target)));
            }
            if (@\file_put_contents($target, $buffer) === false) {
                throw new RuntimeException(
                    \sprintf(
                        'Could not write to "%s',
                        $target
                    )
                );
            }
        }
        return $buffer;
    }
    private function createDirectory(string $directory): bool
    {
        return !(!\is_dir($directory) && !@\mkdir($directory, 0777, true) && !\is_dir($directory));
    }
}
