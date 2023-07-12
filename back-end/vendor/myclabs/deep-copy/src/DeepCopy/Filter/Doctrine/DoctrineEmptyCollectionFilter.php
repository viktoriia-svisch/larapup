<?php
namespace DeepCopy\Filter\Doctrine;
use DeepCopy\Filter\Filter;
use DeepCopy\Reflection\ReflectionHelper;
use Doctrine\Common\Collections\ArrayCollection;
class DoctrineEmptyCollectionFilter implements Filter
{
    public function apply($object, $property, $objectCopier)
    {
        $reflectionProperty = ReflectionHelper::getProperty($object, $property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, new ArrayCollection());
    }
} 
