<?php
namespace SebastianBergmann\CodeCoverage\Node;
use SebastianBergmann\CodeCoverage\Util;
abstract class AbstractNode implements \Countable
{
    private $name;
    private $path;
    private $pathArray;
    private $parent;
    private $id;
    public function __construct(string $name, self $parent = null)
    {
        if (\substr($name, -1) == \DIRECTORY_SEPARATOR) {
            $name = \substr($name, 0, -1);
        }
        $this->name   = $name;
        $this->parent = $parent;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getId(): string
    {
        if ($this->id === null) {
            $parent = $this->getParent();
            if ($parent === null) {
                $this->id = 'index';
            } else {
                $parentId = $parent->getId();
                if ($parentId === 'index') {
                    $this->id = \str_replace(':', '_', $this->name);
                } else {
                    $this->id = $parentId . '/' . $this->name;
                }
            }
        }
        return $this->id;
    }
    public function getPath(): string
    {
        if ($this->path === null) {
            if ($this->parent === null || $this->parent->getPath() === null || $this->parent->getPath() === false) {
                $this->path = $this->name;
            } else {
                $this->path = $this->parent->getPath() . \DIRECTORY_SEPARATOR . $this->name;
            }
        }
        return $this->path;
    }
    public function getPathAsArray(): array
    {
        if ($this->pathArray === null) {
            if ($this->parent === null) {
                $this->pathArray = [];
            } else {
                $this->pathArray = $this->parent->getPathAsArray();
            }
            $this->pathArray[] = $this;
        }
        return $this->pathArray;
    }
    public function getParent(): ?self
    {
        return $this->parent;
    }
    public function getTestedClassesPercent(bool $asString = true)
    {
        return Util::percent(
            $this->getNumTestedClasses(),
            $this->getNumClasses(),
            $asString
        );
    }
    public function getTestedTraitsPercent(bool $asString = true)
    {
        return Util::percent(
            $this->getNumTestedTraits(),
            $this->getNumTraits(),
            $asString
        );
    }
    public function getTestedClassesAndTraitsPercent(bool $asString = true)
    {
        return Util::percent(
            $this->getNumTestedClassesAndTraits(),
            $this->getNumClassesAndTraits(),
            $asString
        );
    }
    public function getTestedFunctionsPercent(bool $asString = true)
    {
        return Util::percent(
            $this->getNumTestedFunctions(),
            $this->getNumFunctions(),
            $asString
        );
    }
    public function getTestedMethodsPercent(bool $asString = true)
    {
        return Util::percent(
            $this->getNumTestedMethods(),
            $this->getNumMethods(),
            $asString
        );
    }
    public function getTestedFunctionsAndMethodsPercent(bool $asString = true)
    {
        return Util::percent(
            $this->getNumTestedFunctionsAndMethods(),
            $this->getNumFunctionsAndMethods(),
            $asString
        );
    }
    public function getLineExecutedPercent(bool $asString = true)
    {
        return Util::percent(
            $this->getNumExecutedLines(),
            $this->getNumExecutableLines(),
            $asString
        );
    }
    public function getNumClassesAndTraits(): int
    {
        return $this->getNumClasses() + $this->getNumTraits();
    }
    public function getNumTestedClassesAndTraits(): int
    {
        return $this->getNumTestedClasses() + $this->getNumTestedTraits();
    }
    public function getClassesAndTraits(): array
    {
        return \array_merge($this->getClasses(), $this->getTraits());
    }
    public function getNumFunctionsAndMethods(): int
    {
        return $this->getNumFunctions() + $this->getNumMethods();
    }
    public function getNumTestedFunctionsAndMethods(): int
    {
        return $this->getNumTestedFunctions() + $this->getNumTestedMethods();
    }
    public function getFunctionsAndMethods(): array
    {
        return \array_merge($this->getFunctions(), $this->getMethods());
    }
    abstract public function getClasses(): array;
    abstract public function getTraits(): array;
    abstract public function getFunctions(): array;
    abstract public function getLinesOfCode(): array;
    abstract public function getNumExecutableLines(): int;
    abstract public function getNumExecutedLines(): int;
    abstract public function getNumClasses(): int;
    abstract public function getNumTestedClasses(): int;
    abstract public function getNumTraits(): int;
    abstract public function getNumTestedTraits(): int;
    abstract public function getNumMethods(): int;
    abstract public function getNumTestedMethods(): int;
    abstract public function getNumFunctions(): int;
    abstract public function getNumTestedFunctions(): int;
}
