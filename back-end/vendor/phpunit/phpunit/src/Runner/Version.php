<?php declare(strict_types=1);
namespace PHPUnit\Runner;
use SebastianBergmann\Version as VersionId;
class Version
{
    private static $pharVersion;
    private static $version;
    public static function id(): string
    {
        if (self::$pharVersion !== null) {
            return self::$pharVersion;
        }
        if (self::$version === null) {
            $version       = new VersionId('7.5.6', \dirname(__DIR__, 2));
            self::$version = $version->getVersion();
        }
        return self::$version;
    }
    public static function series(): string
    {
        if (\strpos(self::id(), '-')) {
            $version = \explode('-', self::id())[0];
        } else {
            $version = self::id();
        }
        return \implode('.', \array_slice(\explode('.', $version), 0, 2));
    }
    public static function getVersionString(): string
    {
        return 'PHPUnit ' . self::id() . ' by Sebastian Bergmann and contributors.';
    }
    public static function getReleaseChannel(): string
    {
        if (\strpos(self::$pharVersion, '-') !== false) {
            return '-nightly';
        }
        return '';
    }
}
