<?php
declare(strict_types=1);
namespace SebastianBergmann\GlobalState;
use ReflectionClass;
use Serializable;
class Snapshot
{
    private $blacklist;
    private $globalVariables = [];
    private $superGlobalArrays = [];
    private $superGlobalVariables = [];
    private $staticAttributes = [];
    private $iniSettings = [];
    private $includedFiles = [];
    private $constants = [];
    private $functions = [];
    private $interfaces = [];
    private $classes = [];
    private $traits = [];
    public function __construct(Blacklist $blacklist = null, bool $includeGlobalVariables = true, bool $includeStaticAttributes = true, bool $includeConstants = true, bool $includeFunctions = true, bool $includeClasses = true, bool $includeInterfaces = true, bool $includeTraits = true, bool $includeIniSettings = true, bool $includeIncludedFiles = true)
    {
        if ($blacklist === null) {
            $blacklist = new Blacklist;
        }
        $this->blacklist = $blacklist;
        if ($includeConstants) {
            $this->snapshotConstants();
        }
        if ($includeFunctions) {
            $this->snapshotFunctions();
        }
        if ($includeClasses || $includeStaticAttributes) {
            $this->snapshotClasses();
        }
        if ($includeInterfaces) {
            $this->snapshotInterfaces();
        }
        if ($includeGlobalVariables) {
            $this->setupSuperGlobalArrays();
            $this->snapshotGlobals();
        }
        if ($includeStaticAttributes) {
            $this->snapshotStaticAttributes();
        }
        if ($includeIniSettings) {
            $this->iniSettings = \ini_get_all(null, false);
        }
        if ($includeIncludedFiles) {
            $this->includedFiles = \get_included_files();
        }
        $this->traits = \get_declared_traits();
    }
    public function blacklist(): Blacklist
    {
        return $this->blacklist;
    }
    public function globalVariables(): array
    {
        return $this->globalVariables;
    }
    public function superGlobalVariables(): array
    {
        return $this->superGlobalVariables;
    }
    public function superGlobalArrays(): array
    {
        return $this->superGlobalArrays;
    }
    public function staticAttributes(): array
    {
        return $this->staticAttributes;
    }
    public function iniSettings(): array
    {
        return $this->iniSettings;
    }
    public function includedFiles(): array
    {
        return $this->includedFiles;
    }
    public function constants(): array
    {
        return $this->constants;
    }
    public function functions(): array
    {
        return $this->functions;
    }
    public function interfaces(): array
    {
        return $this->interfaces;
    }
    public function classes(): array
    {
        return $this->classes;
    }
    public function traits(): array
    {
        return $this->traits;
    }
    private function snapshotConstants()
    {
        $constants = \get_defined_constants(true);
        if (isset($constants['user'])) {
            $this->constants = $constants['user'];
        }
    }
    private function snapshotFunctions()
    {
        $functions = \get_defined_functions();
        $this->functions = $functions['user'];
    }
    private function snapshotClasses()
    {
        foreach (\array_reverse(\get_declared_classes()) as $className) {
            $class = new ReflectionClass($className);
            if (!$class->isUserDefined()) {
                break;
            }
            $this->classes[] = $className;
        }
        $this->classes = \array_reverse($this->classes);
    }
    private function snapshotInterfaces()
    {
        foreach (\array_reverse(\get_declared_interfaces()) as $interfaceName) {
            $class = new ReflectionClass($interfaceName);
            if (!$class->isUserDefined()) {
                break;
            }
            $this->interfaces[] = $interfaceName;
        }
        $this->interfaces = \array_reverse($this->interfaces);
    }
    private function snapshotGlobals()
    {
        $superGlobalArrays = $this->superGlobalArrays();
        foreach ($superGlobalArrays as $superGlobalArray) {
            $this->snapshotSuperGlobalArray($superGlobalArray);
        }
        foreach (\array_keys($GLOBALS) as $key) {
            if ($key != 'GLOBALS' &&
                !\in_array($key, $superGlobalArrays) &&
                $this->canBeSerialized($GLOBALS[$key]) &&
                !$this->blacklist->isGlobalVariableBlacklisted($key)) {
                $this->globalVariables[$key] = \unserialize(\serialize($GLOBALS[$key]));
            }
        }
    }
    private function snapshotSuperGlobalArray(string $superGlobalArray)
    {
        $this->superGlobalVariables[$superGlobalArray] = [];
        if (isset($GLOBALS[$superGlobalArray]) && \is_array($GLOBALS[$superGlobalArray])) {
            foreach ($GLOBALS[$superGlobalArray] as $key => $value) {
                $this->superGlobalVariables[$superGlobalArray][$key] = \unserialize(\serialize($value));
            }
        }
    }
    private function snapshotStaticAttributes()
    {
        foreach ($this->classes as $className) {
            $class    = new ReflectionClass($className);
            $snapshot = [];
            foreach ($class->getProperties() as $attribute) {
                if ($attribute->isStatic()) {
                    $name = $attribute->getName();
                    if ($this->blacklist->isStaticAttributeBlacklisted($className, $name)) {
                        continue;
                    }
                    $attribute->setAccessible(true);
                    $value = $attribute->getValue();
                    if ($this->canBeSerialized($value)) {
                        $snapshot[$name] = \unserialize(\serialize($value));
                    }
                }
            }
            if (!empty($snapshot)) {
                $this->staticAttributes[$className] = $snapshot;
            }
        }
    }
    private function setupSuperGlobalArrays()
    {
        $this->superGlobalArrays = [
            '_ENV',
            '_POST',
            '_GET',
            '_COOKIE',
            '_SERVER',
            '_FILES',
            '_REQUEST'
        ];
        if (\ini_get('register_long_arrays') == '1') {
            $this->superGlobalArrays = \array_merge(
                $this->superGlobalArrays,
                [
                    'HTTP_ENV_VARS',
                    'HTTP_POST_VARS',
                    'HTTP_GET_VARS',
                    'HTTP_COOKIE_VARS',
                    'HTTP_SERVER_VARS',
                    'HTTP_POST_FILES'
                ]
            );
        }
    }
    private function canBeSerialized($variable): bool
    {
        if (!\is_object($variable)) {
            return !\is_resource($variable);
        }
        if ($variable instanceof \stdClass) {
            return true;
        }
        $class = new ReflectionClass($variable);
        do {
            if ($class->isInternal()) {
                return $variable instanceof Serializable;
            }
        } while ($class = $class->getParentClass());
        return true;
    }
}
