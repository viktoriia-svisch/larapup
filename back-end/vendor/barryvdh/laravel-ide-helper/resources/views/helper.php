<?= '<?php' ?>
<?php
?>
<?php foreach($namespaces_by_extends_ns as $namespace => $aliases): ?>
<?php if ($namespace == '\Illuminate\Database\Eloquent'): continue; endif; ?>
namespace <?= $namespace == '__root' ? '' : trim($namespace, '\\') ?> { 
<?php foreach($aliases as $alias): ?>
    <?= trim($alias->getDocComment('    ')) ?> 
    <?= $alias->getClassType() ?> <?= $alias->getExtendsClass() ?> {
        <?php foreach($alias->getMethods() as $method): ?>
        <?= trim($method->getDocComment('        ')) ?> 
        public static function <?= $method->getName() ?>(<?= $method->getParamsWithDefault() ?>)
        {<?php if($method->getDeclaringClass() !== $method->getRoot()): ?>
            <?php endif; ?>
            <?php if($method->isInstanceCall()):?>
            <?php endif?>
            <?= $method->shouldReturn() ? 'return ': '' ?><?= $method->getRootMethodCall() ?>;
        }
        <?php endforeach; ?> 
    }
<?php endforeach; ?> 
}
<?php endforeach; ?>
<?php foreach($namespaces_by_alias_ns as $namespace => $aliases): ?>
namespace <?= $namespace == '__root' ? '' : trim($namespace, '\\') ?> { 
<?php foreach($aliases as $alias): ?>
    <?= $alias->getClassType() ?> <?= $alias->getShortName() ?> extends <?= $alias->getExtends() ?> {<?php if ($alias->getExtendsNamespace() == '\Illuminate\Database\Eloquent'): ?>
        <?php foreach($alias->getMethods() as $method): ?> 
            <?= trim($method->getDocComment('            ')) ?> 
            public static function <?= $method->getName() ?>(<?= $method->getParamsWithDefault() ?>)
            {<?php if($method->getDeclaringClass() !== $method->getRoot()): ?>
                <?php endif; ?>
                <?php if($method->isInstanceCall()):?>
                <?php endif?>
                <?= $method->shouldReturn() ? 'return ': '' ?><?= $method->getRootMethodCall() ?>;
            }
        <?php endforeach; ?>
<?php endif; ?>}
<?php endforeach; ?> 
}
<?php endforeach; ?>
<?php if($helpers): ?>
namespace {
<?= $helpers ?> 
}
<?php endif; ?>
<?php if($include_fluent): ?>
namespace Illuminate\Support {
    class Fluent {}
}
<?php endif ?>
