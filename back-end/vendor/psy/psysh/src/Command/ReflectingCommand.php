<?php
namespace Psy\Command;
use Psy\CodeCleaner\NoReturnValue;
use Psy\Context;
use Psy\ContextAware;
use Psy\Exception\ErrorException;
use Psy\Exception\RuntimeException;
use Psy\Util\Mirror;
abstract class ReflectingCommand extends Command implements ContextAware
{
    const CLASS_OR_FUNC   = '/^[\\\\\w]+$/';
    const CLASS_MEMBER    = '/^([\\\\\w]+)::(\w+)$/';
    const CLASS_STATIC    = '/^([\\\\\w]+)::\$(\w+)$/';
    const INSTANCE_MEMBER = '/^(\$\w+)(::|->)(\w+)$/';
    protected $context;
    public function setContext(Context $context)
    {
        $this->context = $context;
    }
    protected function getTarget($valueName)
    {
        $valueName = \trim($valueName);
        $matches   = [];
        switch (true) {
            case \preg_match(self::CLASS_OR_FUNC, $valueName, $matches):
                return [$this->resolveName($matches[0], true), null, 0];
            case \preg_match(self::CLASS_MEMBER, $valueName, $matches):
                return [$this->resolveName($matches[1]), $matches[2], Mirror::CONSTANT | Mirror::METHOD];
            case \preg_match(self::CLASS_STATIC, $valueName, $matches):
                return [$this->resolveName($matches[1]), $matches[2], Mirror::STATIC_PROPERTY | Mirror::PROPERTY];
            case \preg_match(self::INSTANCE_MEMBER, $valueName, $matches):
                if ($matches[2] === '->') {
                    $kind = Mirror::METHOD | Mirror::PROPERTY;
                } else {
                    $kind = Mirror::CONSTANT | Mirror::METHOD;
                }
                return [$this->resolveObject($matches[1]), $matches[3], $kind];
            default:
                return [$this->resolveObject($valueName), null, 0];
        }
    }
    protected function resolveName($name, $includeFunctions = false)
    {
        $shell = $this->getApplication();
        if (\in_array(\strtolower($name), ['self', 'static'])) {
            if ($boundClass = $shell->getBoundClass()) {
                return $boundClass;
            }
            if ($boundObject = $shell->getBoundObject()) {
                return \get_class($boundObject);
            }
            $msg = \sprintf('Cannot use "%s" when no class scope is active', \strtolower($name));
            throw new ErrorException($msg, 0, E_USER_ERROR, "eval()'d code", 1);
        }
        if (\substr($name, 0, 1) === '\\') {
            return $name;
        }
        if ($namespace = $shell->getNamespace()) {
            $fullName = $namespace . '\\' . $name;
            if (\class_exists($fullName) || \interface_exists($fullName) || ($includeFunctions && \function_exists($fullName))) {
                return $fullName;
            }
        }
        return $name;
    }
    protected function getTargetAndReflector($valueName)
    {
        list($value, $member, $kind) = $this->getTarget($valueName);
        return [$value, Mirror::get($value, $member, $kind)];
    }
    protected function resolveCode($code)
    {
        try {
            $value = $this->getApplication()->execute($code, true);
        } catch (\Exception $e) {
        }
        if (!isset($value) || $value instanceof NoReturnValue) {
            throw new RuntimeException('Unknown target: ' . $code);
        }
        return $value;
    }
    private function resolveObject($code)
    {
        $value = $this->resolveCode($code);
        if (!\is_object($value)) {
            throw new RuntimeException('Unable to inspect a non-object');
        }
        return $value;
    }
    protected function resolveInstance($name)
    {
        @\trigger_error('`resolveInstance` is deprecated; use `resolveCode` instead.', E_USER_DEPRECATED);
        return $this->resolveCode($name);
    }
    protected function getScopeVariable($name)
    {
        return $this->context->get($name);
    }
    protected function getScopeVariables()
    {
        return $this->context->getAll();
    }
    protected function setCommandScopeVariables(\Reflector $reflector)
    {
        $vars = [];
        switch (\get_class($reflector)) {
            case 'ReflectionClass':
            case 'ReflectionObject':
                $vars['__class'] = $reflector->name;
                if ($reflector->inNamespace()) {
                    $vars['__namespace'] = $reflector->getNamespaceName();
                }
                break;
            case 'ReflectionMethod':
                $vars['__method'] = \sprintf('%s::%s', $reflector->class, $reflector->name);
                $vars['__class'] = $reflector->class;
                $classReflector = $reflector->getDeclaringClass();
                if ($classReflector->inNamespace()) {
                    $vars['__namespace'] = $classReflector->getNamespaceName();
                }
                break;
            case 'ReflectionFunction':
                $vars['__function'] = $reflector->name;
                if ($reflector->inNamespace()) {
                    $vars['__namespace'] = $reflector->getNamespaceName();
                }
                break;
            case 'ReflectionGenerator':
                $funcReflector = $reflector->getFunction();
                $vars['__function'] = $funcReflector->name;
                if ($funcReflector->inNamespace()) {
                    $vars['__namespace'] = $funcReflector->getNamespaceName();
                }
                if ($fileName = $reflector->getExecutingFile()) {
                    $vars['__file'] = $fileName;
                    $vars['__line'] = $reflector->getExecutingLine();
                    $vars['__dir']  = \dirname($fileName);
                }
                break;
            case 'ReflectionProperty':
            case 'ReflectionClassConstant':
            case 'Psy\Reflection\ReflectionClassConstant':
                $classReflector = $reflector->getDeclaringClass();
                $vars['__class'] = $classReflector->name;
                if ($classReflector->inNamespace()) {
                    $vars['__namespace'] = $classReflector->getNamespaceName();
                }
                if ($fileName = $reflector->getDeclaringClass()->getFileName()) {
                    $vars['__file'] = $fileName;
                    $vars['__dir']  = \dirname($fileName);
                }
                break;
            case 'Psy\Reflection\ReflectionConstant_':
                if ($reflector->inNamespace()) {
                    $vars['__namespace'] = $reflector->getNamespaceName();
                }
                break;
        }
        if ($reflector instanceof \ReflectionClass || $reflector instanceof \ReflectionFunctionAbstract) {
            if ($fileName = $reflector->getFileName()) {
                $vars['__file'] = $fileName;
                $vars['__line'] = $reflector->getStartLine();
                $vars['__dir']  = \dirname($fileName);
            }
        }
        $this->context->setCommandScopeVariables($vars);
    }
}
