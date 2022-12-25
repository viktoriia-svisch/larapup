<?php
namespace Symfony\Component\VarDumper\Caster;
use Doctrine\Common\Proxy\Proxy as CommonProxy;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Proxy\Proxy as OrmProxy;
use Symfony\Component\VarDumper\Cloner\Stub;
class DoctrineCaster
{
    public static function castCommonProxy(CommonProxy $proxy, array $a, Stub $stub, $isNested)
    {
        foreach (['__cloner__', '__initializer__'] as $k) {
            if (\array_key_exists($k, $a)) {
                unset($a[$k]);
                ++$stub->cut;
            }
        }
        return $a;
    }
    public static function castOrmProxy(OrmProxy $proxy, array $a, Stub $stub, $isNested)
    {
        foreach (['_entityPersister', '_identifier'] as $k) {
            if (\array_key_exists($k = "\0Doctrine\\ORM\\Proxy\\Proxy\0".$k, $a)) {
                unset($a[$k]);
                ++$stub->cut;
            }
        }
        return $a;
    }
    public static function castPersistentCollection(PersistentCollection $coll, array $a, Stub $stub, $isNested)
    {
        foreach (['snapshot', 'association', 'typeClass'] as $k) {
            if (\array_key_exists($k = "\0Doctrine\\ORM\\PersistentCollection\0".$k, $a)) {
                $a[$k] = new CutStub($a[$k]);
            }
        }
        return $a;
    }
}
