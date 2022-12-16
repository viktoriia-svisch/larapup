<?php
namespace Symfony\Component\VarDumper\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
class ArgsStub extends EnumStub
{
    private static $parameters = [];
    public function __construct(array $args, string $function, ?string $class)
    {
        list($variadic, $params) = self::getParameters($function, $class);
        $values = [];
        foreach ($args as $k => $v) {
            $values[$k] = !is_scalar($v) && !$v instanceof Stub ? new CutStub($v) : $v;
        }
        if (null === $params) {
            parent::__construct($values, false);
            return;
        }
        if (\count($values) < \count($params)) {
            $params = \array_slice($params, 0, \count($values));
        } elseif (\count($values) > \count($params)) {
            $values[] = new EnumStub(array_splice($values, \count($params)), false);
            $params[] = $variadic;
        }
        if (['...'] === $params) {
            $this->dumpKeys = false;
            $this->value = $values[0]->value;
        } else {
            $this->value = array_combine($params, $values);
        }
    }
    private static function getParameters($function, $class)
    {
        if (isset(self::$parameters[$k = $class.'::'.$function])) {
            return self::$parameters[$k];
        }
        try {
            $r = null !== $class ? new \ReflectionMethod($class, $function) : new \ReflectionFunction($function);
        } catch (\ReflectionException $e) {
            return [null, null];
        }
        $variadic = '...';
        $params = [];
        foreach ($r->getParameters() as $v) {
            $k = '$'.$v->name;
            if ($v->isPassedByReference()) {
                $k = '&'.$k;
            }
            if ($v->isVariadic()) {
                $variadic .= $k;
            } else {
                $params[] = $k;
            }
        }
        return self::$parameters[$k] = [$variadic, $params];
    }
}
