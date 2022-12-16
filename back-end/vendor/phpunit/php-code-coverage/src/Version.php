<?php
namespace SebastianBergmann\CodeCoverage;
use SebastianBergmann\Version as VersionId;
final class Version
{
    private static $version;
    public static function id(): string
    {
        if (self::$version === null) {
            $version       = new VersionId('6.1.4', \dirname(__DIR__));
            self::$version = $version->getVersion();
        }
        return self::$version;
    }
}
