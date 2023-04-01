<?php
namespace PHPUnit\Util;
use PHPUnit\Framework\TestCase;
class GlobalStateTest extends TestCase
{
    public function testIncludedFilesAsStringSkipsVfsProtocols(): void
    {
        $dir   = __DIR__;
        $files = [
            'phpunit', 
            $dir . '/ConfigurationTest.php',
            $dir . '/GlobalStateTest.php',
            'vfs:
            'phpvfs53e46260465c7:
            'file:
        ];
        $this->assertEquals(
            "require_once '" . $dir . "/ConfigurationTest.php';\n" .
            "require_once '" . $dir . "/GlobalStateTest.php';\n" .
            "require_once 'file:
            GlobalState::processIncludedFilesAsString($files)
        );
    }
}
