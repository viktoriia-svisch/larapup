<?php
namespace Prophecy\Doubler\Generator;
use Prophecy\Exception\Doubler\ClassCreatorException;
class ClassCreator
{
    private $generator;
    public function __construct(ClassCodeGenerator $generator = null)
    {
        $this->generator = $generator ?: new ClassCodeGenerator;
    }
    public function create($classname, Node\ClassNode $class)
    {
        $code = $this->generator->generate($classname, $class);
        $return = eval($code);
        if (!class_exists($classname, false)) {
            if (count($class->getInterfaces())) {
                throw new ClassCreatorException(sprintf(
                    'Could not double `%s` and implement interfaces: [%s].',
                    $class->getParentClass(), implode(', ', $class->getInterfaces())
                ), $class);
            }
            throw new ClassCreatorException(
                sprintf('Could not double `%s`.', $class->getParentClass()),
                $class
            );
        }
        return $return;
    }
}
