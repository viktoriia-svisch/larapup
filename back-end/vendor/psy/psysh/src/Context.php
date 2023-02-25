<?php
namespace Psy;
class Context
{
    private static $specialNames = ['_', '_e', '__out', '__psysh__', 'this'];
    private static $commandScopeNames = [
        '__function', '__method', '__class', '__namespace', '__file', '__line', '__dir',
    ];
    private $scopeVariables = [];
    private $commandScopeVariables = [];
    private $returnValue;
    private $lastException;
    private $lastStdout;
    private $boundObject;
    private $boundClass;
    public function get($name)
    {
        switch ($name) {
            case '_':
                return $this->returnValue;
            case '_e':
                if (isset($this->lastException)) {
                    return $this->lastException;
                }
                break;
            case '__out':
                if (isset($this->lastStdout)) {
                    return $this->lastStdout;
                }
                break;
            case 'this':
                if (isset($this->boundObject)) {
                    return $this->boundObject;
                }
                break;
            case '__function':
            case '__method':
            case '__class':
            case '__namespace':
            case '__file':
            case '__line':
            case '__dir':
                if (\array_key_exists($name, $this->commandScopeVariables)) {
                    return $this->commandScopeVariables[$name];
                }
                break;
            default:
                if (\array_key_exists($name, $this->scopeVariables)) {
                    return $this->scopeVariables[$name];
                }
                break;
        }
        throw new \InvalidArgumentException('Unknown variable: $' . $name);
    }
    public function getAll()
    {
        return \array_merge($this->scopeVariables, $this->getSpecialVariables());
    }
    public function getSpecialVariables()
    {
        $vars = [
            '_' => $this->returnValue,
        ];
        if (isset($this->lastException)) {
            $vars['_e'] = $this->lastException;
        }
        if (isset($this->lastStdout)) {
            $vars['__out'] = $this->lastStdout;
        }
        if (isset($this->boundObject)) {
            $vars['this'] = $this->boundObject;
        }
        return \array_merge($vars, $this->commandScopeVariables);
    }
    public function setAll(array $vars)
    {
        foreach (self::$specialNames as $key) {
            unset($vars[$key]);
        }
        foreach (self::$commandScopeNames as $key) {
            unset($vars[$key]);
        }
        $this->scopeVariables = $vars;
    }
    public function setReturnValue($value)
    {
        $this->returnValue = $value;
    }
    public function getReturnValue()
    {
        return $this->returnValue;
    }
    public function setLastException(\Exception $e)
    {
        $this->lastException = $e;
    }
    public function getLastException()
    {
        if (!isset($this->lastException)) {
            throw new \InvalidArgumentException('No most-recent exception');
        }
        return $this->lastException;
    }
    public function setLastStdout($lastStdout)
    {
        $this->lastStdout = $lastStdout;
    }
    public function getLastStdout()
    {
        if (!isset($this->lastStdout)) {
            throw new \InvalidArgumentException('No most-recent output');
        }
        return $this->lastStdout;
    }
    public function setBoundObject($boundObject)
    {
        $this->boundObject = \is_object($boundObject) ? $boundObject : null;
        $this->boundClass = null;
    }
    public function getBoundObject()
    {
        return $this->boundObject;
    }
    public function setBoundClass($boundClass)
    {
        $this->boundClass = (\is_string($boundClass) && $boundClass !== '') ? $boundClass : null;
        $this->boundObject = null;
    }
    public function getBoundClass()
    {
        return $this->boundClass;
    }
    public function setCommandScopeVariables(array $commandScopeVariables)
    {
        $vars = [];
        foreach ($commandScopeVariables as $key => $value) {
            if (\is_scalar($value) && \in_array($key, self::$commandScopeNames)) {
                $vars[$key] = $value;
            }
        }
        $this->commandScopeVariables = $vars;
    }
    public function getCommandScopeVariables()
    {
        return $this->commandScopeVariables;
    }
    public function getUnusedCommandScopeVariableNames()
    {
        return \array_diff(self::$commandScopeNames, \array_keys($this->commandScopeVariables));
    }
    public static function isSpecialVariableName($name)
    {
        return \in_array($name, self::$specialNames) || \in_array($name, self::$commandScopeNames);
    }
}
