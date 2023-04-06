<?php
declare(strict_types=1);
namespace SebastianBergmann\GlobalState\TestFixture;
use DomDocument;
use ArrayObject;
class SnapshotClass
{
    private static $string = 'snapshot';
    private static $dom;
    private static $closure;
    private static $arrayObject;
    private static $snapshotDomDocument;
    private static $resource;
    private static $stdClass;
    public static function init()
    {
        self::$dom                 = new DomDocument();
        self::$closure             = function () {};
        self::$arrayObject         = new ArrayObject([1, 2, 3]);
        self::$snapshotDomDocument = new SnapshotDomDocument();
        self::$resource            = \fopen('php:
        self::$stdClass            = new \stdClass();
    }
}
