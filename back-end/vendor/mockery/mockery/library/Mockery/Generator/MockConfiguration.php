<?php
namespace Mockery\Generator;
class MockConfiguration
{
    protected static $mockCounter = 0;
    protected $targetClass;
    protected $targetClassName;
    protected $targetInterfaces = array();
    protected $targetInterfaceNames = array();
    protected $targetTraits = array();
    protected $targetTraitNames = array();
    protected $targetObject;
    protected $name;
    protected $blackListedMethods = array();
    protected $whiteListedMethods = array();
    protected $instanceMock = false;
    protected $parameterOverrides = array();
    protected $allMethods;
    protected $mockOriginalDestructor = false;
    protected $constantsMap = array();
    public function __construct(
        array $targets = array(),
        array $blackListedMethods = array(),
        array $whiteListedMethods = array(),
        $name = null,
        $instanceMock = false,
        array $parameterOverrides = array(),
        $mockOriginalDestructor = false,
        array $constantsMap = array()
    ) {
        $this->addTargets($targets);
        $this->blackListedMethods = $blackListedMethods;
        $this->whiteListedMethods = $whiteListedMethods;
        $this->name = $name;
        $this->instanceMock = $instanceMock;
        $this->parameterOverrides = $parameterOverrides;
        $this->mockOriginalDestructor = $mockOriginalDestructor;
        $this->constantsMap = $constantsMap;
    }
    public function getHash()
    {
        $vars = array(
            'targetClassName'        => $this->targetClassName,
            'targetInterfaceNames'   => $this->targetInterfaceNames,
            'targetTraitNames'       => $this->targetTraitNames,
            'name'                   => $this->name,
            'blackListedMethods'     => $this->blackListedMethods,
            'whiteListedMethod'      => $this->whiteListedMethods,
            'instanceMock'           => $this->instanceMock,
            'parameterOverrides'     => $this->parameterOverrides,
            'mockOriginalDestructor' => $this->mockOriginalDestructor
        );
        return md5(serialize($vars));
    }
    public function getMethodsToMock()
    {
        $methods = $this->getAllMethods();
        foreach ($methods as $key => $method) {
            if ($method->isFinal()) {
                unset($methods[$key]);
            }
        }
        if (count($this->getWhiteListedMethods())) {
            $whitelist = array_map('strtolower', $this->getWhiteListedMethods());
            $methods = array_filter($methods, function ($method) use ($whitelist) {
                return $method->isAbstract() || in_array(strtolower($method->getName()), $whitelist);
            });
            return $methods;
        }
        if (count($this->getBlackListedMethods())) {
            $blacklist = array_map('strtolower', $this->getBlackListedMethods());
            $methods = array_filter($methods, function ($method) use ($blacklist) {
                return !in_array(strtolower($method->getName()), $blacklist);
            });
        }
        if ($this->getTargetClass()
            && $this->getTargetClass()->implementsInterface("Serializable")
            && $this->getTargetClass()->hasInternalAncestor()) {
            $methods = array_filter($methods, function ($method) {
                return $method->getName() !== "unserialize";
            });
        }
        return array_values($methods);
    }
    public function requiresCallTypeHintRemoval()
    {
        foreach ($this->getAllMethods() as $method) {
            if ("__call" === $method->getName()) {
                $params = $method->getParameters();
                return !$params[1]->isArray();
            }
        }
        return false;
    }
    public function requiresCallStaticTypeHintRemoval()
    {
        foreach ($this->getAllMethods() as $method) {
            if ("__callStatic" === $method->getName()) {
                $params = $method->getParameters();
                return !$params[1]->isArray();
            }
        }
        return false;
    }
    public function rename($className)
    {
        $targets = array();
        if ($this->targetClassName) {
            $targets[] = $this->targetClassName;
        }
        if ($this->targetInterfaceNames) {
            $targets = array_merge($targets, $this->targetInterfaceNames);
        }
        if ($this->targetTraitNames) {
            $targets = array_merge($targets, $this->targetTraitNames);
        }
        if ($this->targetObject) {
            $targets[] = $this->targetObject;
        }
        return new self(
            $targets,
            $this->blackListedMethods,
            $this->whiteListedMethods,
            $className,
            $this->instanceMock,
            $this->parameterOverrides,
            $this->mockOriginalDestructor,
            $this->constantsMap
        );
    }
    protected function addTarget($target)
    {
        if (is_object($target)) {
            $this->setTargetObject($target);
            $this->setTargetClassName(get_class($target));
            return $this;
        }
        if ($target[0] !== "\\") {
            $target = "\\" . $target;
        }
        if (class_exists($target)) {
            $this->setTargetClassName($target);
            return $this;
        }
        if (interface_exists($target)) {
            $this->addTargetInterfaceName($target);
            return $this;
        }
        if (trait_exists($target)) {
            $this->addTargetTraitName($target);
            return $this;
        }
        if ($this->getTargetClassName()) {
            $this->addTargetInterfaceName($target);
            return $this;
        }
        $this->setTargetClassName($target);
    }
    protected function addTargets($interfaces)
    {
        foreach ($interfaces as $interface) {
            $this->addTarget($interface);
        }
    }
    public function getTargetClassName()
    {
        return $this->targetClassName;
    }
    public function getTargetClass()
    {
        if ($this->targetClass) {
            return $this->targetClass;
        }
        if (!$this->targetClassName) {
            return null;
        }
        if (class_exists($this->targetClassName)) {
            $dtc = DefinedTargetClass::factory($this->targetClassName);
            if ($this->getTargetObject() == false && $dtc->isFinal()) {
                throw new \Mockery\Exception(
                    'The class ' . $this->targetClassName . ' is marked final and its methods'
                    . ' cannot be replaced. Classes marked final can be passed in'
                    . ' to \Mockery::mock() as instantiated objects to create a'
                    . ' partial mock, but only if the mock is not subject to type'
                    . ' hinting checks.'
                );
            }
            $this->targetClass = $dtc;
        } else {
            $this->targetClass = UndefinedTargetClass::factory($this->targetClassName);
        }
        return $this->targetClass;
    }
    public function getTargetTraits()
    {
        if (!empty($this->targetTraits)) {
            return $this->targetTraits;
        }
        foreach ($this->targetTraitNames as $targetTrait) {
            $this->targetTraits[] = DefinedTargetClass::factory($targetTrait);
        }
        $this->targetTraits = array_unique($this->targetTraits); 
        return $this->targetTraits;
    }
    public function getTargetInterfaces()
    {
        if (!empty($this->targetInterfaces)) {
            return $this->targetInterfaces;
        }
        foreach ($this->targetInterfaceNames as $targetInterface) {
            if (!interface_exists($targetInterface)) {
                $this->targetInterfaces[] = UndefinedTargetClass::factory($targetInterface);
                return;
            }
            $dtc = DefinedTargetClass::factory($targetInterface);
            $extendedInterfaces = array_keys($dtc->getInterfaces());
            $extendedInterfaces[] = $targetInterface;
            $traversableFound = false;
            $iteratorShiftedToFront = false;
            foreach ($extendedInterfaces as $interface) {
                if (!$traversableFound && preg_match("/^\\?Iterator(|Aggregate)$/i", $interface)) {
                    break;
                }
                if (preg_match("/^\\\\?IteratorAggregate$/i", $interface)) {
                    $this->targetInterfaces[] = DefinedTargetClass::factory("\\IteratorAggregate");
                    $iteratorShiftedToFront = true;
                } elseif (preg_match("/^\\\\?Iterator$/i", $interface)) {
                    $this->targetInterfaces[] = DefinedTargetClass::factory("\\Iterator");
                    $iteratorShiftedToFront = true;
                } elseif (preg_match("/^\\\\?Traversable$/i", $interface)) {
                    $traversableFound = true;
                }
            }
            if ($traversableFound && !$iteratorShiftedToFront) {
                $this->targetInterfaces[] = DefinedTargetClass::factory("\\IteratorAggregate");
            }
            if (!preg_match("/^\\\\?Traversable$/i", $targetInterface)) {
                $this->targetInterfaces[] = $dtc;
            }
        }
        $this->targetInterfaces = array_unique($this->targetInterfaces); 
        return $this->targetInterfaces;
    }
    public function getTargetObject()
    {
        return $this->targetObject;
    }
    public function getName()
    {
        return $this->name;
    }
    public function generateName()
    {
        $name = 'Mockery_' . static::$mockCounter++;
        if ($this->getTargetObject()) {
            $name .= "_" . str_replace("\\", "_", get_class($this->getTargetObject()));
        }
        if ($this->getTargetClass()) {
            $name .= "_" . str_replace("\\", "_", $this->getTargetClass()->getName());
        }
        if ($this->getTargetInterfaces()) {
            $name .= array_reduce($this->getTargetInterfaces(), function ($tmpname, $i) {
                $tmpname .= '_' . str_replace("\\", "_", $i->getName());
                return $tmpname;
            }, '');
        }
        return $name;
    }
    public function getShortName()
    {
        $parts = explode("\\", $this->getName());
        return array_pop($parts);
    }
    public function getNamespaceName()
    {
        $parts = explode("\\", $this->getName());
        array_pop($parts);
        if (count($parts)) {
            return implode("\\", $parts);
        }
        return "";
    }
    public function getBlackListedMethods()
    {
        return $this->blackListedMethods;
    }
    public function getWhiteListedMethods()
    {
        return $this->whiteListedMethods;
    }
    public function isInstanceMock()
    {
        return $this->instanceMock;
    }
    public function getParameterOverrides()
    {
        return $this->parameterOverrides;
    }
    public function isMockOriginalDestructor()
    {
        return $this->mockOriginalDestructor;
    }
    protected function setTargetClassName($targetClassName)
    {
        $this->targetClassName = $targetClassName;
    }
    protected function getAllMethods()
    {
        if ($this->allMethods) {
            return $this->allMethods;
        }
        $classes = $this->getTargetInterfaces();
        if ($this->getTargetClass()) {
            $classes[] = $this->getTargetClass();
        }
        $methods = array();
        foreach ($classes as $class) {
            $methods = array_merge($methods, $class->getMethods());
        }
        foreach ($this->getTargetTraits() as $trait) {
            foreach ($trait->getMethods() as $method) {
                if ($method->isAbstract()) {
                    $methods[] = $method;
                }
            }
        }
        $names = array();
        $methods = array_filter($methods, function ($method) use (&$names) {
            if (in_array($method->getName(), $names)) {
                return false;
            }
            $names[] = $method->getName();
            return true;
        });
        if (defined('HHVM_VERSION')) {
            $methods = array_filter($methods, function ($method) {
                return strpos($method->getName(), '$memoize_impl') === false;
            });
        }
        return $this->allMethods = $methods;
    }
    protected function addTargetInterfaceName($targetInterface)
    {
        $this->targetInterfaceNames[] = $targetInterface;
    }
    protected function addTargetTraitName($targetTraitName)
    {
        $this->targetTraitNames[] = $targetTraitName;
    }
    protected function setTargetObject($object)
    {
        $this->targetObject = $object;
    }
    public function getConstantsMap()
    {
        return $this->constantsMap;
    }
}
