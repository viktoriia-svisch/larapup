<?php
namespace Symfony\Component\Process\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\PhpExecutableFinder;
class PhpExecutableFinderTest extends TestCase
{
    public function testFind()
    {
        $f = new PhpExecutableFinder();
        $current = PHP_BINARY;
        $args = 'phpdbg' === \PHP_SAPI ? ' -qrr' : '';
        $this->assertEquals($current.$args, $f->find(), '::find() returns the executable PHP');
        $this->assertEquals($current, $f->find(false), '::find() returns the executable PHP');
    }
    public function testFindArguments()
    {
        $f = new PhpExecutableFinder();
        if ('phpdbg' === \PHP_SAPI) {
            $this->assertEquals($f->findArguments(), ['-qrr'], '::findArguments() returns phpdbg arguments');
        } else {
            $this->assertEquals($f->findArguments(), [], '::findArguments() returns no arguments');
        }
    }
}
