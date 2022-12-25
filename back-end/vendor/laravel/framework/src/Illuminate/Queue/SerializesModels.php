<?php
namespace Illuminate\Queue;
use ReflectionClass;
use ReflectionProperty;
trait SerializesModels
{
    use SerializesAndRestoresModelIdentifiers;
    public function __sleep()
    {
        $properties = (new ReflectionClass($this))->getProperties();
        foreach ($properties as $property) {
            $property->setValue($this, $this->getSerializedPropertyValue(
                $this->getPropertyValue($property)
            ));
        }
        return array_values(array_filter(array_map(function ($p) {
            return $p->isStatic() ? null : $p->getName();
        }, $properties)));
    }
    public function __wakeup()
    {
        foreach ((new ReflectionClass($this))->getProperties() as $property) {
            if ($property->isStatic()) {
                continue;
            }
            $property->setValue($this, $this->getRestoredPropertyValue(
                $this->getPropertyValue($property)
            ));
        }
    }
    protected function getPropertyValue(ReflectionProperty $property)
    {
        $property->setAccessible(true);
        return $property->getValue($this);
    }
}
