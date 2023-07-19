<?php
namespace SebastianBergmann\CodeCoverage;
use PHPUnit\Framework\TestCase;
use PHPUnit\Runner\PhptTestCase;
use PHPUnit\Util\Test;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Driver\PHPDBG;
use SebastianBergmann\CodeCoverage\Driver\Xdebug;
use SebastianBergmann\CodeCoverage\Node\Builder;
use SebastianBergmann\CodeCoverage\Node\Directory;
use SebastianBergmann\CodeUnitReverseLookup\Wizard;
use SebastianBergmann\Environment\Runtime;
final class CodeCoverage
{
    private $driver;
    private $filter;
    private $wizard;
    private $cacheTokens = false;
    private $checkForUnintentionallyCoveredCode = false;
    private $forceCoversAnnotation = false;
    private $checkForUnexecutedCoveredCode = false;
    private $checkForMissingCoversAnnotation = false;
    private $addUncoveredFilesFromWhitelist = true;
    private $processUncoveredFilesFromWhitelist = false;
    private $ignoreDeprecatedCode = false;
    private $currentId;
    private $data = [];
    private $ignoredLines = [];
    private $disableIgnoredLines = false;
    private $tests = [];
    private $unintentionallyCoveredSubclassesWhitelist = [];
    private $isInitialized = false;
    private $shouldCheckForDeadAndUnused = true;
    private $report;
    public function __construct(Driver $driver = null, Filter $filter = null)
    {
        if ($filter === null) {
            $filter = new Filter;
        }
        if ($driver === null) {
            $driver = $this->selectDriver($filter);
        }
        $this->driver = $driver;
        $this->filter = $filter;
        $this->wizard = new Wizard;
    }
    public function getReport(): Directory
    {
        if ($this->report === null) {
            $builder = new Builder;
            $this->report = $builder->build($this);
        }
        return $this->report;
    }
    public function clear(): void
    {
        $this->isInitialized = false;
        $this->currentId     = null;
        $this->data          = [];
        $this->tests         = [];
        $this->report        = null;
    }
    public function filter(): Filter
    {
        return $this->filter;
    }
    public function getData(bool $raw = false): array
    {
        if (!$raw && $this->addUncoveredFilesFromWhitelist) {
            $this->addUncoveredFilesFromWhitelist();
        }
        return $this->data;
    }
    public function setData(array $data): void
    {
        $this->data   = $data;
        $this->report = null;
    }
    public function getTests(): array
    {
        return $this->tests;
    }
    public function setTests(array $tests): void
    {
        $this->tests = $tests;
    }
    public function start($id, bool $clear = false): void
    {
        if ($clear) {
            $this->clear();
        }
        if ($this->isInitialized === false) {
            $this->initializeData();
        }
        $this->currentId = $id;
        $this->driver->start($this->shouldCheckForDeadAndUnused);
    }
    public function stop(bool $append = true, $linesToBeCovered = [], array $linesToBeUsed = [], bool $ignoreForceCoversAnnotation = false): array
    {
        if (!\is_array($linesToBeCovered) && $linesToBeCovered !== false) {
            throw InvalidArgumentException::create(
                2,
                'array or false'
            );
        }
        $data = $this->driver->stop();
        $this->append($data, null, $append, $linesToBeCovered, $linesToBeUsed, $ignoreForceCoversAnnotation);
        $this->currentId = null;
        return $data;
    }
    public function append(array $data, $id = null, bool $append = true, $linesToBeCovered = [], array $linesToBeUsed = [], bool $ignoreForceCoversAnnotation = false): void
    {
        if ($id === null) {
            $id = $this->currentId;
        }
        if ($id === null) {
            throw new RuntimeException;
        }
        $this->applyWhitelistFilter($data);
        $this->applyIgnoredLinesFilter($data);
        $this->initializeFilesThatAreSeenTheFirstTime($data);
        if (!$append) {
            return;
        }
        if ($id !== 'UNCOVERED_FILES_FROM_WHITELIST') {
            $this->applyCoversAnnotationFilter(
                $data,
                $linesToBeCovered,
                $linesToBeUsed,
                $ignoreForceCoversAnnotation
            );
        }
        if (empty($data)) {
            return;
        }
        $size   = 'unknown';
        $status = -1;
        if ($id instanceof TestCase) {
            $_size = $id->getSize();
            if ($_size === Test::SMALL) {
                $size = 'small';
            } elseif ($_size === Test::MEDIUM) {
                $size = 'medium';
            } elseif ($_size === Test::LARGE) {
                $size = 'large';
            }
            $status = $id->getStatus();
            $id     = \get_class($id) . '::' . $id->getName();
        } elseif ($id instanceof PhptTestCase) {
            $size = 'large';
            $id   = $id->getName();
        }
        $this->tests[$id] = ['size' => $size, 'status' => $status];
        foreach ($data as $file => $lines) {
            if (!$this->filter->isFile($file)) {
                continue;
            }
            foreach ($lines as $k => $v) {
                if ($v === Driver::LINE_EXECUTED) {
                    if (empty($this->data[$file][$k]) || !\in_array($id, $this->data[$file][$k])) {
                        $this->data[$file][$k][] = $id;
                    }
                }
            }
        }
        $this->report = null;
    }
    public function merge(self $that): void
    {
        $this->filter->setWhitelistedFiles(
            \array_merge($this->filter->getWhitelistedFiles(), $that->filter()->getWhitelistedFiles())
        );
        foreach ($that->data as $file => $lines) {
            if (!isset($this->data[$file])) {
                if (!$this->filter->isFiltered($file)) {
                    $this->data[$file] = $lines;
                }
                continue;
            }
            $compareLineNumbers = \array_unique(
                \array_merge(
                    \array_keys($this->data[$file]),
                    \array_keys($that->data[$file])
                )
            );
            foreach ($compareLineNumbers as $line) {
                $thatPriority = $this->getLinePriority($that->data[$file], $line);
                $thisPriority = $this->getLinePriority($this->data[$file], $line);
                if ($thatPriority > $thisPriority) {
                    $this->data[$file][$line] = $that->data[$file][$line];
                } elseif ($thatPriority === $thisPriority && \is_array($this->data[$file][$line])) {
                    $this->data[$file][$line] = \array_unique(
                        \array_merge($this->data[$file][$line], $that->data[$file][$line])
                    );
                }
            }
        }
        $this->tests  = \array_merge($this->tests, $that->getTests());
        $this->report = null;
    }
    public function setCacheTokens(bool $flag): void
    {
        $this->cacheTokens = $flag;
    }
    public function getCacheTokens(): bool
    {
        return $this->cacheTokens;
    }
    public function setCheckForUnintentionallyCoveredCode(bool $flag): void
    {
        $this->checkForUnintentionallyCoveredCode = $flag;
    }
    public function setForceCoversAnnotation(bool $flag): void
    {
        $this->forceCoversAnnotation = $flag;
    }
    public function setCheckForMissingCoversAnnotation(bool $flag): void
    {
        $this->checkForMissingCoversAnnotation = $flag;
    }
    public function setCheckForUnexecutedCoveredCode(bool $flag): void
    {
        $this->checkForUnexecutedCoveredCode = $flag;
    }
    public function setAddUncoveredFilesFromWhitelist(bool $flag): void
    {
        $this->addUncoveredFilesFromWhitelist = $flag;
    }
    public function setProcessUncoveredFilesFromWhitelist(bool $flag): void
    {
        $this->processUncoveredFilesFromWhitelist = $flag;
    }
    public function setDisableIgnoredLines(bool $flag): void
    {
        $this->disableIgnoredLines = $flag;
    }
    public function setIgnoreDeprecatedCode(bool $flag): void
    {
        $this->ignoreDeprecatedCode = $flag;
    }
    public function setUnintentionallyCoveredSubclassesWhitelist(array $whitelist): void
    {
        $this->unintentionallyCoveredSubclassesWhitelist = $whitelist;
    }
    private function getLinePriority($data, $line)
    {
        if (!\array_key_exists($line, $data)) {
            return 1;
        }
        if (\is_array($data[$line]) && \count($data[$line]) === 0) {
            return 2;
        }
        if ($data[$line] === null) {
            return 3;
        }
        return 4;
    }
    private function applyCoversAnnotationFilter(array &$data, $linesToBeCovered, array $linesToBeUsed, bool $ignoreForceCoversAnnotation): void
    {
        if ($linesToBeCovered === false ||
            ($this->forceCoversAnnotation && empty($linesToBeCovered) && !$ignoreForceCoversAnnotation)) {
            if ($this->checkForMissingCoversAnnotation) {
                throw new MissingCoversAnnotationException;
            }
            $data = [];
            return;
        }
        if (empty($linesToBeCovered)) {
            return;
        }
        if ($this->checkForUnintentionallyCoveredCode &&
            (!$this->currentId instanceof TestCase ||
            (!$this->currentId->isMedium() && !$this->currentId->isLarge()))) {
            $this->performUnintentionallyCoveredCodeCheck($data, $linesToBeCovered, $linesToBeUsed);
        }
        if ($this->checkForUnexecutedCoveredCode) {
            $this->performUnexecutedCoveredCodeCheck($data, $linesToBeCovered, $linesToBeUsed);
        }
        $data = \array_intersect_key($data, $linesToBeCovered);
        foreach (\array_keys($data) as $filename) {
            $_linesToBeCovered = \array_flip($linesToBeCovered[$filename]);
            $data[$filename]   = \array_intersect_key($data[$filename], $_linesToBeCovered);
        }
    }
    private function applyWhitelistFilter(array &$data): void
    {
        foreach (\array_keys($data) as $filename) {
            if ($this->filter->isFiltered($filename)) {
                unset($data[$filename]);
            }
        }
    }
    private function applyIgnoredLinesFilter(array &$data): void
    {
        foreach (\array_keys($data) as $filename) {
            if (!$this->filter->isFile($filename)) {
                continue;
            }
            foreach ($this->getLinesToBeIgnored($filename) as $line) {
                unset($data[$filename][$line]);
            }
        }
    }
    private function initializeFilesThatAreSeenTheFirstTime(array $data): void
    {
        foreach ($data as $file => $lines) {
            if (!isset($this->data[$file]) && $this->filter->isFile($file)) {
                $this->data[$file] = [];
                foreach ($lines as $k => $v) {
                    $this->data[$file][$k] = $v === -2 ? null : [];
                }
            }
        }
    }
    private function addUncoveredFilesFromWhitelist(): void
    {
        $data           = [];
        $uncoveredFiles = \array_diff(
            $this->filter->getWhitelist(),
            \array_keys($this->data)
        );
        foreach ($uncoveredFiles as $uncoveredFile) {
            if (!\file_exists($uncoveredFile)) {
                continue;
            }
            $data[$uncoveredFile] = [];
            $lines = \count(\file($uncoveredFile));
            for ($i = 1; $i <= $lines; $i++) {
                $data[$uncoveredFile][$i] = Driver::LINE_NOT_EXECUTED;
            }
        }
        $this->append($data, 'UNCOVERED_FILES_FROM_WHITELIST');
    }
    private function getLinesToBeIgnored(string $fileName): array
    {
        if (isset($this->ignoredLines[$fileName])) {
            return $this->ignoredLines[$fileName];
        }
        try {
            return $this->getLinesToBeIgnoredInner($fileName);
        } catch (\OutOfBoundsException $e) {
            return [];
        }
    }
    private function getLinesToBeIgnoredInner(string $fileName): array
    {
        $this->ignoredLines[$fileName] = [];
        $lines = \file($fileName);
        foreach ($lines as $index => $line) {
            if (!\trim($line)) {
                $this->ignoredLines[$fileName][] = $index + 1;
            }
        }
        if ($this->cacheTokens) {
            $tokens = \PHP_Token_Stream_CachingFactory::get($fileName);
        } else {
            $tokens = new \PHP_Token_Stream($fileName);
        }
        foreach ($tokens->getInterfaces() as $interface) {
            $interfaceStartLine = $interface['startLine'];
            $interfaceEndLine   = $interface['endLine'];
            foreach (\range($interfaceStartLine, $interfaceEndLine) as $line) {
                $this->ignoredLines[$fileName][] = $line;
            }
        }
        foreach (\array_merge($tokens->getClasses(), $tokens->getTraits()) as $classOrTrait) {
            $classOrTraitStartLine = $classOrTrait['startLine'];
            $classOrTraitEndLine   = $classOrTrait['endLine'];
            if (empty($classOrTrait['methods'])) {
                foreach (\range($classOrTraitStartLine, $classOrTraitEndLine) as $line) {
                    $this->ignoredLines[$fileName][] = $line;
                }
                continue;
            }
            $firstMethod          = \array_shift($classOrTrait['methods']);
            $firstMethodStartLine = $firstMethod['startLine'];
            $firstMethodEndLine   = $firstMethod['endLine'];
            $lastMethodEndLine    = $firstMethodEndLine;
            do {
                $lastMethod = \array_pop($classOrTrait['methods']);
            } while ($lastMethod !== null && 0 === \strpos($lastMethod['signature'], 'anonymousFunction'));
            if ($lastMethod !== null) {
                $lastMethodEndLine = $lastMethod['endLine'];
            }
            foreach (\range($classOrTraitStartLine, $firstMethodStartLine) as $line) {
                $this->ignoredLines[$fileName][] = $line;
            }
            foreach (\range($lastMethodEndLine + 1, $classOrTraitEndLine) as $line) {
                $this->ignoredLines[$fileName][] = $line;
            }
        }
        if ($this->disableIgnoredLines) {
            $this->ignoredLines[$fileName] = \array_unique($this->ignoredLines[$fileName]);
            \sort($this->ignoredLines[$fileName]);
            return $this->ignoredLines[$fileName];
        }
        $ignore = false;
        $stop   = false;
        foreach ($tokens->tokens() as $token) {
            switch (\get_class($token)) {
                case \PHP_Token_COMMENT::class:
                case \PHP_Token_DOC_COMMENT::class:
                    $_token = \trim($token);
                    $_line  = \trim($lines[$token->getLine() - 1]);
                    if ($_token === '
                        $_token === '
                        $ignore = true;
                        $stop   = true;
                    } elseif ($_token === '
                        $_token === '
                        $ignore = true;
                    } elseif ($_token === '
                        $_token === '
                        $stop = true;
                    }
                    if (!$ignore) {
                        $start = $token->getLine();
                        $end   = $start + \substr_count($token, "\n");
                        if (0 !== \strpos($_token, $_line)) {
                            $start++;
                        }
                        for ($i = $start; $i < $end; $i++) {
                            $this->ignoredLines[$fileName][] = $i;
                        }
                        if (isset($lines[$i - 1]) && 0 === \strpos($_token, '' === \substr(\trim($lines[$i - 1]), -2)) {
                            $this->ignoredLines[$fileName][] = $i;
                        }
                    }
                    break;
                case \PHP_Token_INTERFACE::class:
                case \PHP_Token_TRAIT::class:
                case \PHP_Token_CLASS::class:
                case \PHP_Token_FUNCTION::class:
                    $docblock = $token->getDocblock();
                    $this->ignoredLines[$fileName][] = $token->getLine();
                    if (\strpos($docblock, '@codeCoverageIgnore') || ($this->ignoreDeprecatedCode && \strpos($docblock, '@deprecated'))) {
                        $endLine = $token->getEndLine();
                        for ($i = $token->getLine(); $i <= $endLine; $i++) {
                            $this->ignoredLines[$fileName][] = $i;
                        }
                    }
                    break;
                case \PHP_Token_NAMESPACE::class:
                    $this->ignoredLines[$fileName][] = $token->getEndLine();
                case \PHP_Token_DECLARE::class:
                case \PHP_Token_OPEN_TAG::class:
                case \PHP_Token_CLOSE_TAG::class:
                case \PHP_Token_USE::class:
                    $this->ignoredLines[$fileName][] = $token->getLine();
                    break;
            }
            if ($ignore) {
                $this->ignoredLines[$fileName][] = $token->getLine();
                if ($stop) {
                    $ignore = false;
                    $stop   = false;
                }
            }
        }
        $this->ignoredLines[$fileName][] = \count($lines) + 1;
        $this->ignoredLines[$fileName] = \array_unique(
            $this->ignoredLines[$fileName]
        );
        $this->ignoredLines[$fileName] = \array_unique($this->ignoredLines[$fileName]);
        \sort($this->ignoredLines[$fileName]);
        return $this->ignoredLines[$fileName];
    }
    private function performUnintentionallyCoveredCodeCheck(array &$data, array $linesToBeCovered, array $linesToBeUsed): void
    {
        $allowedLines = $this->getAllowedLines(
            $linesToBeCovered,
            $linesToBeUsed
        );
        $unintentionallyCoveredUnits = [];
        foreach ($data as $file => $_data) {
            foreach ($_data as $line => $flag) {
                if ($flag === 1 && !isset($allowedLines[$file][$line])) {
                    $unintentionallyCoveredUnits[] = $this->wizard->lookup($file, $line);
                }
            }
        }
        $unintentionallyCoveredUnits = $this->processUnintentionallyCoveredUnits($unintentionallyCoveredUnits);
        if (!empty($unintentionallyCoveredUnits)) {
            throw new UnintentionallyCoveredCodeException(
                $unintentionallyCoveredUnits
            );
        }
    }
    private function performUnexecutedCoveredCodeCheck(array &$data, array $linesToBeCovered, array $linesToBeUsed): void
    {
        $executedCodeUnits = $this->coverageToCodeUnits($data);
        $message           = '';
        foreach ($this->linesToCodeUnits($linesToBeCovered) as $codeUnit) {
            if (!\in_array($codeUnit, $executedCodeUnits)) {
                $message .= \sprintf(
                    '- %s is expected to be executed (@covers) but was not executed' . "\n",
                    $codeUnit
                );
            }
        }
        foreach ($this->linesToCodeUnits($linesToBeUsed) as $codeUnit) {
            if (!\in_array($codeUnit, $executedCodeUnits)) {
                $message .= \sprintf(
                    '- %s is expected to be executed (@uses) but was not executed' . "\n",
                    $codeUnit
                );
            }
        }
        if (!empty($message)) {
            throw new CoveredCodeNotExecutedException($message);
        }
    }
    private function getAllowedLines(array $linesToBeCovered, array $linesToBeUsed): array
    {
        $allowedLines = [];
        foreach (\array_keys($linesToBeCovered) as $file) {
            if (!isset($allowedLines[$file])) {
                $allowedLines[$file] = [];
            }
            $allowedLines[$file] = \array_merge(
                $allowedLines[$file],
                $linesToBeCovered[$file]
            );
        }
        foreach (\array_keys($linesToBeUsed) as $file) {
            if (!isset($allowedLines[$file])) {
                $allowedLines[$file] = [];
            }
            $allowedLines[$file] = \array_merge(
                $allowedLines[$file],
                $linesToBeUsed[$file]
            );
        }
        foreach (\array_keys($allowedLines) as $file) {
            $allowedLines[$file] = \array_flip(
                \array_unique($allowedLines[$file])
            );
        }
        return $allowedLines;
    }
    private function selectDriver(Filter $filter): Driver
    {
        $runtime = new Runtime;
        if (!$runtime->canCollectCodeCoverage()) {
            throw new RuntimeException('No code coverage driver available');
        }
        if ($runtime->isPHPDBG()) {
            return new PHPDBG;
        }
        if ($runtime->hasXdebug()) {
            return new Xdebug($filter);
        }
        throw new RuntimeException('No code coverage driver available');
    }
    private function processUnintentionallyCoveredUnits(array $unintentionallyCoveredUnits): array
    {
        $unintentionallyCoveredUnits = \array_unique($unintentionallyCoveredUnits);
        \sort($unintentionallyCoveredUnits);
        foreach (\array_keys($unintentionallyCoveredUnits) as $k => $v) {
            $unit = \explode('::', $unintentionallyCoveredUnits[$k]);
            if (\count($unit) !== 2) {
                continue;
            }
            $class = new \ReflectionClass($unit[0]);
            foreach ($this->unintentionallyCoveredSubclassesWhitelist as $whitelisted) {
                if ($class->isSubclassOf($whitelisted)) {
                    unset($unintentionallyCoveredUnits[$k]);
                    break;
                }
            }
        }
        return \array_values($unintentionallyCoveredUnits);
    }
    private function initializeData(): void
    {
        $this->isInitialized = true;
        if ($this->processUncoveredFilesFromWhitelist) {
            $this->shouldCheckForDeadAndUnused = false;
            $this->driver->start();
            foreach ($this->filter->getWhitelist() as $file) {
                if ($this->filter->isFile($file)) {
                    include_once $file;
                }
            }
            $data     = [];
            $coverage = $this->driver->stop();
            foreach ($coverage as $file => $fileCoverage) {
                if ($this->filter->isFiltered($file)) {
                    continue;
                }
                foreach (\array_keys($fileCoverage) as $key) {
                    if ($fileCoverage[$key] === Driver::LINE_EXECUTED) {
                        $fileCoverage[$key] = Driver::LINE_NOT_EXECUTED;
                    }
                }
                $data[$file] = $fileCoverage;
            }
            $this->append($data, 'UNCOVERED_FILES_FROM_WHITELIST');
        }
    }
    private function coverageToCodeUnits(array $data): array
    {
        $codeUnits = [];
        foreach ($data as $filename => $lines) {
            foreach ($lines as $line => $flag) {
                if ($flag === 1) {
                    $codeUnits[] = $this->wizard->lookup($filename, $line);
                }
            }
        }
        return \array_unique($codeUnits);
    }
    private function linesToCodeUnits(array $data): array
    {
        $codeUnits = [];
        foreach ($data as $filename => $lines) {
            foreach ($lines as $line) {
                $codeUnits[] = $this->wizard->lookup($filename, $line);
            }
        }
        return \array_unique($codeUnits);
    }
}