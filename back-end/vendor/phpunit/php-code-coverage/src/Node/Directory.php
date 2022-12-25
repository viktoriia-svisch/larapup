<?php
namespace SebastianBergmann\CodeCoverage\Node;
use SebastianBergmann\CodeCoverage\InvalidArgumentException;
final class Directory extends AbstractNode implements \IteratorAggregate
{
    private $children = [];
    private $directories = [];
    private $files = [];
    private $classes;
    private $traits;
    private $functions;
    private $linesOfCode;
    private $numFiles = -1;
    private $numExecutableLines = -1;
    private $numExecutedLines = -1;
    private $numClasses = -1;
    private $numTestedClasses = -1;
    private $numTraits = -1;
    private $numTestedTraits = -1;
    private $numMethods = -1;
    private $numTestedMethods = -1;
    private $numFunctions = -1;
    private $numTestedFunctions = -1;
    public function count(): int
    {
        if ($this->numFiles === -1) {
            $this->numFiles = 0;
            foreach ($this->children as $child) {
                $this->numFiles += \count($child);
            }
        }
        return $this->numFiles;
    }
    public function getIterator(): \RecursiveIteratorIterator
    {
        return new \RecursiveIteratorIterator(
            new Iterator($this),
            \RecursiveIteratorIterator::SELF_FIRST
        );
    }
    public function addDirectory(string $name): self
    {
        $directory = new self($name, $this);
        $this->children[]    = $directory;
        $this->directories[] = &$this->children[\count($this->children) - 1];
        return $directory;
    }
    public function addFile(string $name, array $coverageData, array $testData, bool $cacheTokens): File
    {
        $file = new File($name, $this, $coverageData, $testData, $cacheTokens);
        $this->children[] = $file;
        $this->files[]    = &$this->children[\count($this->children) - 1];
        $this->numExecutableLines = -1;
        $this->numExecutedLines   = -1;
        return $file;
    }
    public function getDirectories(): array
    {
        return $this->directories;
    }
    public function getFiles(): array
    {
        return $this->files;
    }
    public function getChildNodes(): array
    {
        return $this->children;
    }
    public function getClasses(): array
    {
        if ($this->classes === null) {
            $this->classes = [];
            foreach ($this->children as $child) {
                $this->classes = \array_merge(
                    $this->classes,
                    $child->getClasses()
                );
            }
        }
        return $this->classes;
    }
    public function getTraits(): array
    {
        if ($this->traits === null) {
            $this->traits = [];
            foreach ($this->children as $child) {
                $this->traits = \array_merge(
                    $this->traits,
                    $child->getTraits()
                );
            }
        }
        return $this->traits;
    }
    public function getFunctions(): array
    {
        if ($this->functions === null) {
            $this->functions = [];
            foreach ($this->children as $child) {
                $this->functions = \array_merge(
                    $this->functions,
                    $child->getFunctions()
                );
            }
        }
        return $this->functions;
    }
    public function getLinesOfCode(): array
    {
        if ($this->linesOfCode === null) {
            $this->linesOfCode = ['loc' => 0, 'cloc' => 0, 'ncloc' => 0];
            foreach ($this->children as $child) {
                $linesOfCode = $child->getLinesOfCode();
                $this->linesOfCode['loc'] += $linesOfCode['loc'];
                $this->linesOfCode['cloc'] += $linesOfCode['cloc'];
                $this->linesOfCode['ncloc'] += $linesOfCode['ncloc'];
            }
        }
        return $this->linesOfCode;
    }
    public function getNumExecutableLines(): int
    {
        if ($this->numExecutableLines === -1) {
            $this->numExecutableLines = 0;
            foreach ($this->children as $child) {
                $this->numExecutableLines += $child->getNumExecutableLines();
            }
        }
        return $this->numExecutableLines;
    }
    public function getNumExecutedLines(): int
    {
        if ($this->numExecutedLines === -1) {
            $this->numExecutedLines = 0;
            foreach ($this->children as $child) {
                $this->numExecutedLines += $child->getNumExecutedLines();
            }
        }
        return $this->numExecutedLines;
    }
    public function getNumClasses(): int
    {
        if ($this->numClasses === -1) {
            $this->numClasses = 0;
            foreach ($this->children as $child) {
                $this->numClasses += $child->getNumClasses();
            }
        }
        return $this->numClasses;
    }
    public function getNumTestedClasses(): int
    {
        if ($this->numTestedClasses === -1) {
            $this->numTestedClasses = 0;
            foreach ($this->children as $child) {
                $this->numTestedClasses += $child->getNumTestedClasses();
            }
        }
        return $this->numTestedClasses;
    }
    public function getNumTraits(): int
    {
        if ($this->numTraits === -1) {
            $this->numTraits = 0;
            foreach ($this->children as $child) {
                $this->numTraits += $child->getNumTraits();
            }
        }
        return $this->numTraits;
    }
    public function getNumTestedTraits(): int
    {
        if ($this->numTestedTraits === -1) {
            $this->numTestedTraits = 0;
            foreach ($this->children as $child) {
                $this->numTestedTraits += $child->getNumTestedTraits();
            }
        }
        return $this->numTestedTraits;
    }
    public function getNumMethods(): int
    {
        if ($this->numMethods === -1) {
            $this->numMethods = 0;
            foreach ($this->children as $child) {
                $this->numMethods += $child->getNumMethods();
            }
        }
        return $this->numMethods;
    }
    public function getNumTestedMethods(): int
    {
        if ($this->numTestedMethods === -1) {
            $this->numTestedMethods = 0;
            foreach ($this->children as $child) {
                $this->numTestedMethods += $child->getNumTestedMethods();
            }
        }
        return $this->numTestedMethods;
    }
    public function getNumFunctions(): int
    {
        if ($this->numFunctions === -1) {
            $this->numFunctions = 0;
            foreach ($this->children as $child) {
                $this->numFunctions += $child->getNumFunctions();
            }
        }
        return $this->numFunctions;
    }
    public function getNumTestedFunctions(): int
    {
        if ($this->numTestedFunctions === -1) {
            $this->numTestedFunctions = 0;
            foreach ($this->children as $child) {
                $this->numTestedFunctions += $child->getNumTestedFunctions();
            }
        }
        return $this->numTestedFunctions;
    }
}
