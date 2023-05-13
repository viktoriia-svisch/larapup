<?php
namespace Prophecy\Doubler;
use ReflectionClass;
class NameGenerator
{
    private static $counter = 1;
    public function name(ReflectionClass $class = null, array $interfaces)
    {
        $parts = array();
        if (null !== $class) {
            $parts[] = $class->getName();
        } else {
            foreach ($interfaces as $interface) {
                $parts[] = $interface->getShortName();
            }
        }
        if (!count($parts)) {
            $parts[] = 'stdClass';
        }
        return sprintf('Double\%s\P%d', implode('\\', $parts), self::$counter++);
    }
}
