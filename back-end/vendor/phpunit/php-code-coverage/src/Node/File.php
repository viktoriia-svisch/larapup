<?php
namespace SebastianBergmann\CodeCoverage\Node;
final class File extends AbstractNode
{
    private $coverageData;
    private $testData;
    private $numExecutableLines = 0;
    private $numExecutedLines = 0;
    private $classes = [];
    private $traits = [];
    private $functions = [];
    private $linesOfCode = [];
    private $numClasses;
    private $numTestedClasses = 0;
    private $numTraits;
    private $numTestedTraits = 0;
    private $numMethods;
    private $numTestedMethods;
    private $numTestedFunctions;
    private $cacheTokens;
    private $codeUnitsByLine = [];
    public function __construct(string $name, AbstractNode $parent, array $coverageData, array $testData, bool $cacheTokens)
    {
        parent::__construct($name, $parent);
        $this->coverageData = $coverageData;
        $this->testData     = $testData;
        $this->cacheTokens  = $cacheTokens;
        $this->calculateStatistics();
    }
    public function count(): int
    {
        return 1;
    }
    public function getCoverageData(): array
    {
        return $this->coverageData;
    }
    public function getTestData(): array
    {
        return $this->testData;
    }
    public function getClasses(): array
    {
        return $this->classes;
    }
    public function getTraits(): array
    {
        return $this->traits;
    }
    public function getFunctions(): array
    {
        return $this->functions;
    }
    public function getLinesOfCode(): array
    {
        return $this->linesOfCode;
    }
    public function getNumExecutableLines(): int
    {
        return $this->numExecutableLines;
    }
    public function getNumExecutedLines(): int
    {
        return $this->numExecutedLines;
    }
    public function getNumClasses(): int
    {
        if ($this->numClasses === null) {
            $this->numClasses = 0;
            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numClasses++;
                        continue 2;
                    }
                }
            }
        }
        return $this->numClasses;
    }
    public function getNumTestedClasses(): int
    {
        return $this->numTestedClasses;
    }
    public function getNumTraits(): int
    {
        if ($this->numTraits === null) {
            $this->numTraits = 0;
            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numTraits++;
                        continue 2;
                    }
                }
            }
        }
        return $this->numTraits;
    }
    public function getNumTestedTraits(): int
    {
        return $this->numTestedTraits;
    }
    public function getNumMethods(): int
    {
        if ($this->numMethods === null) {
            $this->numMethods = 0;
            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numMethods++;
                    }
                }
            }
            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0) {
                        $this->numMethods++;
                    }
                }
            }
        }
        return $this->numMethods;
    }
    public function getNumTestedMethods(): int
    {
        if ($this->numTestedMethods === null) {
            $this->numTestedMethods = 0;
            foreach ($this->classes as $class) {
                foreach ($class['methods'] as $method) {
                    if ($method['executableLines'] > 0 &&
                        $method['coverage'] === 100) {
                        $this->numTestedMethods++;
                    }
                }
            }
            foreach ($this->traits as $trait) {
                foreach ($trait['methods'] as $method) {
                    if ($method['executableLines'] > 0 &&
                        $method['coverage'] === 100) {
                        $this->numTestedMethods++;
                    }
                }
            }
        }
        return $this->numTestedMethods;
    }
    public function getNumFunctions(): int
    {
        return \count($this->functions);
    }
    public function getNumTestedFunctions(): int
    {
        if ($this->numTestedFunctions === null) {
            $this->numTestedFunctions = 0;
            foreach ($this->functions as $function) {
                if ($function['executableLines'] > 0 &&
                    $function['coverage'] === 100) {
                    $this->numTestedFunctions++;
                }
            }
        }
        return $this->numTestedFunctions;
    }
    private function calculateStatistics(): void
    {
        if ($this->cacheTokens) {
            $tokens = \PHP_Token_Stream_CachingFactory::get($this->getPath());
        } else {
            $tokens = new \PHP_Token_Stream($this->getPath());
        }
        $this->linesOfCode = $tokens->getLinesOfCode();
        foreach (\range(1, $this->linesOfCode['loc']) as $lineNumber) {
            $this->codeUnitsByLine[$lineNumber] = [];
        }
        try {
            $this->processClasses($tokens);
            $this->processTraits($tokens);
            $this->processFunctions($tokens);
        } catch (\OutOfBoundsException $e) {
        }
        unset($tokens);
        foreach (\range(1, $this->linesOfCode['loc']) as $lineNumber) {
            if (isset($this->coverageData[$lineNumber])) {
                foreach ($this->codeUnitsByLine[$lineNumber] as &$codeUnit) {
                    $codeUnit['executableLines']++;
                }
                unset($codeUnit);
                $this->numExecutableLines++;
                if (\count($this->coverageData[$lineNumber]) > 0) {
                    foreach ($this->codeUnitsByLine[$lineNumber] as &$codeUnit) {
                        $codeUnit['executedLines']++;
                    }
                    unset($codeUnit);
                    $this->numExecutedLines++;
                }
            }
        }
        foreach ($this->traits as &$trait) {
            foreach ($trait['methods'] as &$method) {
                if ($method['executableLines'] > 0) {
                    $method['coverage'] = ($method['executedLines'] /
                            $method['executableLines']) * 100;
                } else {
                    $method['coverage'] = 100;
                }
                $method['crap'] = $this->crap(
                    $method['ccn'],
                    $method['coverage']
                );
                $trait['ccn'] += $method['ccn'];
            }
            unset($method);
            if ($trait['executableLines'] > 0) {
                $trait['coverage'] = ($trait['executedLines'] /
                        $trait['executableLines']) * 100;
                if ($trait['coverage'] === 100) {
                    $this->numTestedClasses++;
                }
            } else {
                $trait['coverage'] = 100;
            }
            $trait['crap'] = $this->crap(
                $trait['ccn'],
                $trait['coverage']
            );
        }
        unset($trait);
        foreach ($this->classes as &$class) {
            foreach ($class['methods'] as &$method) {
                if ($method['executableLines'] > 0) {
                    $method['coverage'] = ($method['executedLines'] /
                            $method['executableLines']) * 100;
                } else {
                    $method['coverage'] = 100;
                }
                $method['crap'] = $this->crap(
                    $method['ccn'],
                    $method['coverage']
                );
                $class['ccn'] += $method['ccn'];
            }
            unset($method);
            if ($class['executableLines'] > 0) {
                $class['coverage'] = ($class['executedLines'] /
                        $class['executableLines']) * 100;
                if ($class['coverage'] === 100) {
                    $this->numTestedClasses++;
                }
            } else {
                $class['coverage'] = 100;
            }
            $class['crap'] = $this->crap(
                $class['ccn'],
                $class['coverage']
            );
        }
        unset($class);
        foreach ($this->functions as &$function) {
            if ($function['executableLines'] > 0) {
                $function['coverage'] = ($function['executedLines'] /
                        $function['executableLines']) * 100;
            } else {
                $function['coverage'] = 100;
            }
            if ($function['coverage'] === 100) {
                $this->numTestedFunctions++;
            }
            $function['crap'] = $this->crap(
                $function['ccn'],
                $function['coverage']
            );
        }
    }
    private function processClasses(\PHP_Token_Stream $tokens): void
    {
        $classes = $tokens->getClasses();
        $link    = $this->getId() . '.html#';
        foreach ($classes as $className => $class) {
            if (\strpos($className, 'anonymous') === 0) {
                continue;
            }
            if (!empty($class['package']['namespace'])) {
                $className = $class['package']['namespace'] . '\\' . $className;
            }
            $this->classes[$className] = [
                'className'       => $className,
                'methods'         => [],
                'startLine'       => $class['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => 0,
                'coverage'        => 0,
                'crap'            => 0,
                'package'         => $class['package'],
                'link'            => $link . $class['startLine'],
            ];
            foreach ($class['methods'] as $methodName => $method) {
                if (\strpos($methodName, 'anonymous') === 0) {
                    continue;
                }
                $this->classes[$className]['methods'][$methodName] = $this->newMethod($methodName, $method, $link);
                foreach (\range($method['startLine'], $method['endLine']) as $lineNumber) {
                    $this->codeUnitsByLine[$lineNumber] = [
                        &$this->classes[$className],
                        &$this->classes[$className]['methods'][$methodName],
                    ];
                }
            }
        }
    }
    private function processTraits(\PHP_Token_Stream $tokens): void
    {
        $traits = $tokens->getTraits();
        $link   = $this->getId() . '.html#';
        foreach ($traits as $traitName => $trait) {
            $this->traits[$traitName] = [
                'traitName'       => $traitName,
                'methods'         => [],
                'startLine'       => $trait['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => 0,
                'coverage'        => 0,
                'crap'            => 0,
                'package'         => $trait['package'],
                'link'            => $link . $trait['startLine'],
            ];
            foreach ($trait['methods'] as $methodName => $method) {
                if (\strpos($methodName, 'anonymous') === 0) {
                    continue;
                }
                $this->traits[$traitName]['methods'][$methodName] = $this->newMethod($methodName, $method, $link);
                foreach (\range($method['startLine'], $method['endLine']) as $lineNumber) {
                    $this->codeUnitsByLine[$lineNumber] = [
                        &$this->traits[$traitName],
                        &$this->traits[$traitName]['methods'][$methodName],
                    ];
                }
            }
        }
    }
    private function processFunctions(\PHP_Token_Stream $tokens): void
    {
        $functions = $tokens->getFunctions();
        $link      = $this->getId() . '.html#';
        foreach ($functions as $functionName => $function) {
            if (\strpos($functionName, 'anonymous') === 0) {
                continue;
            }
            $this->functions[$functionName] = [
                'functionName'    => $functionName,
                'signature'       => $function['signature'],
                'startLine'       => $function['startLine'],
                'executableLines' => 0,
                'executedLines'   => 0,
                'ccn'             => $function['ccn'],
                'coverage'        => 0,
                'crap'            => 0,
                'link'            => $link . $function['startLine'],
            ];
            foreach (\range($function['startLine'], $function['endLine']) as $lineNumber) {
                $this->codeUnitsByLine[$lineNumber] = [&$this->functions[$functionName]];
            }
        }
    }
    private function crap(int $ccn, float $coverage): string
    {
        if ($coverage === 0) {
            return (string) ($ccn ** 2 + $ccn);
        }
        if ($coverage >= 95) {
            return (string) $ccn;
        }
        return \sprintf(
            '%01.2F',
            $ccn ** 2 * (1 - $coverage / 100) ** 3 + $ccn
        );
    }
    private function newMethod(string $methodName, array $method, string $link): array
    {
        return [
            'methodName'      => $methodName,
            'visibility'      => $method['visibility'],
            'signature'       => $method['signature'],
            'startLine'       => $method['startLine'],
            'endLine'         => $method['endLine'],
            'executableLines' => 0,
            'executedLines'   => 0,
            'ccn'             => $method['ccn'],
            'coverage'        => 0,
            'crap'            => 0,
            'link'            => $link . $method['startLine'],
        ];
    }
}
