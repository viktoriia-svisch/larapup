<?php
namespace SebastianBergmann\CodeCoverage\Driver;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\RuntimeException;
final class Xdebug implements Driver
{
    private $cacheNumLines = [];
    private $filter;
    public function __construct(Filter $filter = null)
    {
        if (!\extension_loaded('xdebug')) {
            throw new RuntimeException('This driver requires Xdebug');
        }
        if (!\ini_get('xdebug.coverage_enable')) {
            throw new RuntimeException('xdebug.coverage_enable=On has to be set in php.ini');
        }
        if ($filter === null) {
            $filter = new Filter;
        }
        $this->filter = $filter;
    }
    public function start(bool $determineUnusedAndDead = true): void
    {
        if ($determineUnusedAndDead) {
            \xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
        } else {
            \xdebug_start_code_coverage();
        }
    }
    public function stop(): array
    {
        $data = \xdebug_get_code_coverage();
        \xdebug_stop_code_coverage();
        return $this->cleanup($data);
    }
    private function cleanup(array $data): array
    {
        foreach (\array_keys($data) as $file) {
            unset($data[$file][0]);
            if (!$this->filter->isFile($file)) {
                continue;
            }
            $numLines = $this->getNumberOfLinesInFile($file);
            foreach (\array_keys($data[$file]) as $line) {
                if ($line > $numLines) {
                    unset($data[$file][$line]);
                }
            }
        }
        return $data;
    }
    private function getNumberOfLinesInFile(string $fileName): int
    {
        if (!isset($this->cacheNumLines[$fileName])) {
            $buffer = \file_get_contents($fileName);
            $lines  = \substr_count($buffer, "\n");
            if (\substr($buffer, -1) !== "\n") {
                $lines++;
            }
            $this->cacheNumLines[$fileName] = $lines;
        }
        return $this->cacheNumLines[$fileName];
    }
}