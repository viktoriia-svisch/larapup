<?php
namespace Mockery;
interface MockInterface
{
    public function allows($something = []);
    public function expects($something = null);
    public function mockery_init(\Mockery\Container $container = null, $partialObject = null);
    public function shouldReceive(...$methodNames);
    public function shouldNotReceive(...$methodNames);
    public function shouldAllowMockingMethod($method);
    public function shouldIgnoreMissing($returnValue = null);
    public function shouldAllowMockingProtectedMethods();
    public function shouldDeferMissing();
    public function makePartial();
    public function shouldHaveReceived($method = null, $args = null);
    public function shouldHaveBeenCalled();
    public function shouldNotHaveReceived($method, $args = null);
    public function shouldNotHaveBeenCalled(array $args = null);
    public function byDefault();
    public function mockery_verify();
    public function mockery_teardown();
    public function mockery_allocateOrder();
    public function mockery_setGroup($group, $order);
    public function mockery_getGroups();
    public function mockery_setCurrentOrder($order);
    public function mockery_getCurrentOrder();
    public function mockery_validateOrder($method, $order);
    public function mockery_getExpectationCount();
    public function mockery_setExpectationsFor($method, \Mockery\ExpectationDirector $director);
    public function mockery_getExpectationsFor($method);
    public function mockery_findExpectation($method, array $args);
    public function mockery_getContainer();
    public function mockery_getName();
    public function mockery_getMockableProperties();
    public function mockery_getMockableMethods();
    public function mockery_isAnonymous();
}
