<?php
namespace Symfony\Component\Translation\Tests\Loader;
use PHPUnit\Framework\TestCase;
abstract class LocalizedTestCase extends TestCase
{
    protected function setUp()
    {
        if (!\extension_loaded('intl')) {
            $this->markTestSkipped('Extension intl is required.');
        }
    }
}
