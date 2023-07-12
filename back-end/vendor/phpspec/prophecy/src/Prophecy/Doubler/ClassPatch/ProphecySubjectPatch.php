<?php
namespace Prophecy\Doubler\ClassPatch;
use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Doubler\Generator\Node\MethodNode;
use Prophecy\Doubler\Generator\Node\ArgumentNode;
class ProphecySubjectPatch implements ClassPatchInterface
{
    public function supports(ClassNode $node)
    {
        return true;
    }
    public function apply(ClassNode $node)
    {
        $node->addInterface('Prophecy\Prophecy\ProphecySubjectInterface');
        $node->addProperty('objectProphecy', 'private');
        foreach ($node->getMethods() as $name => $method) {
            if ('__construct' === strtolower($name)) {
                continue;
            }
            if ($method->getReturnType() === 'void') {
                $method->setCode(
                    '$this->getProphecy()->makeProphecyMethodCall(__FUNCTION__, func_get_args());'
                );
            } else {
                $method->setCode(
                    'return $this->getProphecy()->makeProphecyMethodCall(__FUNCTION__, func_get_args());'
                );
            }
        }
        $prophecySetter = new MethodNode('setProphecy');
        $prophecyArgument = new ArgumentNode('prophecy');
        $prophecyArgument->setTypeHint('Prophecy\Prophecy\ProphecyInterface');
        $prophecySetter->addArgument($prophecyArgument);
        $prophecySetter->setCode('$this->objectProphecy = $prophecy;');
        $prophecyGetter = new MethodNode('getProphecy');
        $prophecyGetter->setCode('return $this->objectProphecy;');
        if ($node->hasMethod('__call')) {
            $__call = $node->getMethod('__call');
        } else {
            $__call = new MethodNode('__call');
            $__call->addArgument(new ArgumentNode('name'));
            $__call->addArgument(new ArgumentNode('arguments'));
            $node->addMethod($__call, true);
        }
        $__call->setCode(<<<PHP
throw new \Prophecy\Exception\Doubler\MethodNotFoundException(
    sprintf('Method `%s::%s()` not found.', get_class(\$this), func_get_arg(0)),
    \$this->getProphecy(), func_get_arg(0)
);
PHP
        );
        $node->addMethod($prophecySetter, true);
        $node->addMethod($prophecyGetter, true);
    }
    public function getPriority()
    {
        return 0;
    }
}
