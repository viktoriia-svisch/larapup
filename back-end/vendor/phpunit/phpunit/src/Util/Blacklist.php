<?php
namespace PHPUnit\Util;
use Composer\Autoload\ClassLoader;
use DeepCopy\DeepCopy;
use Doctrine\Instantiator\Instantiator;
use PharIo\Manifest\Manifest;
use PharIo\Version\Version as PharIoVersion;
use PHP_Token;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\Project;
use phpDocumentor\Reflection\Type;
use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use ReflectionClass;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeUnitReverseLookup\Wizard;
use SebastianBergmann\Comparator\Comparator;
use SebastianBergmann\Diff\Diff;
use SebastianBergmann\Environment\Runtime;
use SebastianBergmann\Exporter\Exporter;
use SebastianBergmann\FileIterator\Facade as FileIteratorFacade;
use SebastianBergmann\GlobalState\Snapshot;
use SebastianBergmann\Invoker\Invoker;
use SebastianBergmann\ObjectEnumerator\Enumerator;
use SebastianBergmann\RecursionContext\Context;
use SebastianBergmann\ResourceOperations\ResourceOperations;
use SebastianBergmann\Timer\Timer;
use SebastianBergmann\Version;
use Text_Template;
use TheSeer\Tokenizer\Tokenizer;
use Webmozart\Assert\Assert;
final class Blacklist
{
    public static $blacklistedClassNames = [
        ClassLoader::class => 1,
        Instantiator::class => 1,
        DeepCopy::class => 1,
        Manifest::class => 1,
        PharIoVersion::class => 1,
        Project::class => 1,
        DocBlock::class => 1,
        Type::class => 1,
        Prophet::class => 1,
        TestCase::class => 2,
        CodeCoverage::class => 1,
        FileIteratorFacade::class => 1,
        Invoker::class => 1,
        Text_Template::class => 1,
        Timer::class => 1,
        PHP_Token::class => 1,
        Wizard::class => 1,
        Comparator::class => 1,
        Diff::class => 1,
        Runtime::class => 1,
        Exporter::class => 1,
        Snapshot::class => 1,
        Enumerator::class => 1,
        Context::class => 1,
        ResourceOperations::class => 1,
        Version::class => 1,
        Tokenizer::class => 1,
        Assert::class => 1,
    ];
    private static $directories;
    public function getBlacklistedDirectories(): array
    {
        $this->initialize();
        return self::$directories;
    }
    public function isBlacklisted(string $file): bool
    {
        if (\defined('PHPUNIT_TESTSUITE')) {
            return false;
        }
        $this->initialize();
        foreach (self::$directories as $directory) {
            if (\strpos($file, $directory) === 0) {
                return true;
            }
        }
        return false;
    }
    private function initialize(): void
    {
        if (self::$directories === null) {
            self::$directories = [];
            foreach (self::$blacklistedClassNames as $className => $parent) {
                if (!\class_exists($className)) {
                    continue;
                }
                $reflector = new ReflectionClass($className);
                $directory = $reflector->getFileName();
                for ($i = 0; $i < $parent; $i++) {
                    $directory = \dirname($directory);
                }
                self::$directories[] = $directory;
            }
            if (\DIRECTORY_SEPARATOR === '\\') {
                self::$directories[] = \sys_get_temp_dir() . '\\PHP';
            }
        }
    }
}
