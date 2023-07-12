<?php
class Swift_DependencyContainer
{
    const TYPE_VALUE = 0x00001;
    const TYPE_INSTANCE = 0x00010;
    const TYPE_SHARED = 0x00100;
    const TYPE_ALIAS = 0x01000;
    const TYPE_ARRAY = 0x10000;
    private static $instance = null;
    private $store = [];
    private $endPoint;
    public function __construct()
    {
    }
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function listItems()
    {
        return array_keys($this->store);
    }
    public function has($itemName)
    {
        return array_key_exists($itemName, $this->store)
            && isset($this->store[$itemName]['lookupType']);
    }
    public function lookup($itemName)
    {
        if (!$this->has($itemName)) {
            throw new Swift_DependencyException(
                'Cannot lookup dependency "'.$itemName.'" since it is not registered.'
                );
        }
        switch ($this->store[$itemName]['lookupType']) {
            case self::TYPE_ALIAS:
                return $this->createAlias($itemName);
            case self::TYPE_VALUE:
                return $this->getValue($itemName);
            case self::TYPE_INSTANCE:
                return $this->createNewInstance($itemName);
            case self::TYPE_SHARED:
                return $this->createSharedInstance($itemName);
            case self::TYPE_ARRAY:
                return $this->createDependenciesFor($itemName);
        }
    }
    public function createDependenciesFor($itemName)
    {
        $args = [];
        if (isset($this->store[$itemName]['args'])) {
            $args = $this->resolveArgs($this->store[$itemName]['args']);
        }
        return $args;
    }
    public function register($itemName)
    {
        $this->store[$itemName] = [];
        $this->endPoint = &$this->store[$itemName];
        return $this;
    }
    public function asValue($value)
    {
        $endPoint = &$this->getEndPoint();
        $endPoint['lookupType'] = self::TYPE_VALUE;
        $endPoint['value'] = $value;
        return $this;
    }
    public function asAliasOf($lookup)
    {
        $endPoint = &$this->getEndPoint();
        $endPoint['lookupType'] = self::TYPE_ALIAS;
        $endPoint['ref'] = $lookup;
        return $this;
    }
    public function asNewInstanceOf($className)
    {
        $endPoint = &$this->getEndPoint();
        $endPoint['lookupType'] = self::TYPE_INSTANCE;
        $endPoint['className'] = $className;
        return $this;
    }
    public function asSharedInstanceOf($className)
    {
        $endPoint = &$this->getEndPoint();
        $endPoint['lookupType'] = self::TYPE_SHARED;
        $endPoint['className'] = $className;
        return $this;
    }
    public function asArray()
    {
        $endPoint = &$this->getEndPoint();
        $endPoint['lookupType'] = self::TYPE_ARRAY;
        return $this;
    }
    public function withDependencies(array $lookups)
    {
        $endPoint = &$this->getEndPoint();
        $endPoint['args'] = [];
        foreach ($lookups as $lookup) {
            $this->addConstructorLookup($lookup);
        }
        return $this;
    }
    public function addConstructorValue($value)
    {
        $endPoint = &$this->getEndPoint();
        if (!isset($endPoint['args'])) {
            $endPoint['args'] = [];
        }
        $endPoint['args'][] = ['type' => 'value', 'item' => $value];
        return $this;
    }
    public function addConstructorLookup($lookup)
    {
        $endPoint = &$this->getEndPoint();
        if (!isset($this->endPoint['args'])) {
            $endPoint['args'] = [];
        }
        $endPoint['args'][] = ['type' => 'lookup', 'item' => $lookup];
        return $this;
    }
    private function getValue($itemName)
    {
        return $this->store[$itemName]['value'];
    }
    private function createAlias($itemName)
    {
        return $this->lookup($this->store[$itemName]['ref']);
    }
    private function createNewInstance($itemName)
    {
        $reflector = new ReflectionClass($this->store[$itemName]['className']);
        if ($reflector->getConstructor()) {
            return $reflector->newInstanceArgs(
                $this->createDependenciesFor($itemName)
                );
        }
        return $reflector->newInstance();
    }
    private function createSharedInstance($itemName)
    {
        if (!isset($this->store[$itemName]['instance'])) {
            $this->store[$itemName]['instance'] = $this->createNewInstance($itemName);
        }
        return $this->store[$itemName]['instance'];
    }
    private function &getEndPoint()
    {
        if (!isset($this->endPoint)) {
            throw new BadMethodCallException(
                'Component must first be registered by calling register()'
                );
        }
        return $this->endPoint;
    }
    private function resolveArgs(array $args)
    {
        $resolved = [];
        foreach ($args as $argDefinition) {
            switch ($argDefinition['type']) {
                case 'lookup':
                    $resolved[] = $this->lookupRecursive($argDefinition['item']);
                    break;
                case 'value':
                    $resolved[] = $argDefinition['item'];
                    break;
            }
        }
        return $resolved;
    }
    private function lookupRecursive($item)
    {
        if (is_array($item)) {
            $collection = [];
            foreach ($item as $k => $v) {
                $collection[$k] = $this->lookupRecursive($v);
            }
            return $collection;
        }
        return $this->lookup($item);
    }
}
