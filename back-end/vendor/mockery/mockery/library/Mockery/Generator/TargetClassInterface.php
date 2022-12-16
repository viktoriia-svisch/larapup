<?php
namespace Mockery\Generator;
interface TargetClassInterface
{
    public static function factory($name);
    public function getName();
    public function getMethods();
    public function getInterfaces();
    public function getNamespaceName();
    public function getShortName();
    public function isAbstract();
    public function isFinal();
    public function inNamespace();
    public function implementsInterface($interface);
    public function hasInternalAncestor();
}
