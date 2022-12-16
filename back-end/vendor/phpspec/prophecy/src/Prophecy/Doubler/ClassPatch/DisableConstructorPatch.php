<?php
namespace Prophecy\Doubler\ClassPatch;
use Prophecy\Doubler\Generator\Node\ClassNode;
use Prophecy\Doubler\Generator\Node\MethodNode;
class DisableConstructorPatch implements ClassPatchInterface
{
    public function supports(ClassNode $node)
    {
        return true;
    }
    public function apply(ClassNode $node)
    {
        if (!$node->hasMethod('__construct')) {
            $node->addMethod(new MethodNode('__construct', ''));
            return;
        }
        $constructor = $node->getMethod('__construct');
        foreach ($constructor->getArguments() as $argument) {
            $argument->setDefault(null);
        }
        $constructor->setCode(<<<PHP
if (0 < func_num_args()) {
    call_user_func_array(array('parent', '__construct'), func_get_args());
}
PHP
        );
    }
    public function getPriority()
    {
        return 100;
    }
}
