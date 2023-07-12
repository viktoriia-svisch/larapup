<?php
namespace Mockery;
class Configuration
{
    protected $_allowMockingNonExistentMethod = true;
    protected $_allowMockingMethodsUnnecessarily = true;
    protected $_internalClassParamMap = array();
    protected $_constantsMap = array();
    protected $_reflectionCacheEnabled = true;
    public function allowMockingNonExistentMethods($flag = true)
    {
        $this->_allowMockingNonExistentMethod = (bool) $flag;
    }
    public function mockingNonExistentMethodsAllowed()
    {
        return $this->_allowMockingNonExistentMethod;
    }
    public function allowMockingMethodsUnnecessarily($flag = true)
    {
        trigger_error(sprintf("The %s method is deprecated and will be removed in a future version of Mockery", __METHOD__), E_USER_DEPRECATED);
        $this->_allowMockingMethodsUnnecessarily = (bool) $flag;
    }
    public function mockingMethodsUnnecessarilyAllowed()
    {
        trigger_error(sprintf("The %s method is deprecated and will be removed in a future version of Mockery", __METHOD__), E_USER_DEPRECATED);
        return $this->_allowMockingMethodsUnnecessarily;
    }
    public function setInternalClassMethodParamMap($class, $method, array $map)
    {
        if (!isset($this->_internalClassParamMap[strtolower($class)])) {
            $this->_internalClassParamMap[strtolower($class)] = array();
        }
        $this->_internalClassParamMap[strtolower($class)][strtolower($method)] = $map;
    }
    public function resetInternalClassMethodParamMaps()
    {
        $this->_internalClassParamMap = array();
    }
    public function getInternalClassMethodParamMap($class, $method)
    {
        if (isset($this->_internalClassParamMap[strtolower($class)][strtolower($method)])) {
            return $this->_internalClassParamMap[strtolower($class)][strtolower($method)];
        }
    }
    public function getInternalClassMethodParamMaps()
    {
        return $this->_internalClassParamMap;
    }
    public function setConstantsMap(array $map)
    {
        $this->_constantsMap = $map;
    }
    public function getConstantsMap()
    {
        return $this->_constantsMap;
    }
    public function disableReflectionCache()
    {
        $this->_reflectionCacheEnabled = false;
    }
    public function enableReflectionCache()
    {
        $this->_reflectionCacheEnabled = true;
    }
    public function reflectionCacheEnabled()
    {
        return $this->_reflectionCacheEnabled;
    }
}
