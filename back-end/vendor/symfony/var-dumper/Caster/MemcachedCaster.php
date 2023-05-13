<?php
namespace Symfony\Component\VarDumper\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
class MemcachedCaster
{
    private static $optionConstants;
    private static $defaultOptions;
    public static function castMemcached(\Memcached $c, array $a, Stub $stub, $isNested)
    {
        $a += [
            Caster::PREFIX_VIRTUAL.'servers' => $c->getServerList(),
            Caster::PREFIX_VIRTUAL.'options' => new EnumStub(
                self::getNonDefaultOptions($c)
            ),
        ];
        return $a;
    }
    private static function getNonDefaultOptions(\Memcached $c)
    {
        self::$defaultOptions = self::$defaultOptions ?? self::discoverDefaultOptions();
        self::$optionConstants = self::$optionConstants ?? self::getOptionConstants();
        $nonDefaultOptions = [];
        foreach (self::$optionConstants as $constantKey => $value) {
            if (self::$defaultOptions[$constantKey] !== $option = $c->getOption($value)) {
                $nonDefaultOptions[$constantKey] = $option;
            }
        }
        return $nonDefaultOptions;
    }
    private static function discoverDefaultOptions()
    {
        $defaultMemcached = new \Memcached();
        $defaultMemcached->addServer('127.0.0.1', 11211);
        $defaultOptions = [];
        self::$optionConstants = self::$optionConstants ?? self::getOptionConstants();
        foreach (self::$optionConstants as $constantKey => $value) {
            $defaultOptions[$constantKey] = $defaultMemcached->getOption($value);
        }
        return $defaultOptions;
    }
    private static function getOptionConstants()
    {
        $reflectedMemcached = new \ReflectionClass(\Memcached::class);
        $optionConstants = [];
        foreach ($reflectedMemcached->getConstants() as $constantKey => $value) {
            if (0 === strpos($constantKey, 'OPT_')) {
                $optionConstants[$constantKey] = $value;
            }
        }
        return $optionConstants;
    }
}
