<?php
namespace Barryvdh\Reflection\DocBlock;
class Context
{
    protected $namespace = '';
    protected $namespace_aliases = array();
    protected $lsen = '';
    public function __construct(
        $namespace = '',
        array $namespace_aliases = array(),
        $lsen = ''
    ) {
        if (!empty($namespace)) {
            $this->setNamespace($namespace);
        }
        $this->setNamespaceAliases($namespace_aliases);
        $this->setLSEN($lsen);
    }
    public function getNamespace()
    {
        return $this->namespace;
    }
    public function getNamespaceAliases()
    {
        return $this->namespace_aliases;
    }
    public function getLSEN()
    {
        return $this->lsen;
    }
    public function setNamespace($namespace)
    {
        if ('global' !== $namespace
            && 'default' !== $namespace
        ) {
            $this->namespace = trim((string)$namespace, '\\');
        } else {
            $this->namespace = '';
        }
        return $this;
    }
    public function setNamespaceAliases(array $namespace_aliases)
    {
        $this->namespace_aliases = array();
        foreach ($namespace_aliases as $alias => $fqnn) {
            $this->setNamespaceAlias($alias, $fqnn);
        }
        return $this;
    }
    public function setNamespaceAlias($alias, $fqnn)
    {
        $this->namespace_aliases[$alias] = '\\' . trim((string)$fqnn, '\\');
        return $this;
    }
    public function setLSEN($lsen)
    {
        $this->lsen = (string)$lsen;
        return $this;
    }
}
