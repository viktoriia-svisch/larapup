<?php
namespace Prophecy\Comparator;
use SebastianBergmann\Comparator\Factory as BaseFactory;
final class Factory extends BaseFactory
{
    private static $instance;
    public function __construct()
    {
        parent::__construct();
        $this->register(new ClosureComparator());
        $this->register(new ProphecyComparator());
    }
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Factory;
        }
        return self::$instance;
    }
}
