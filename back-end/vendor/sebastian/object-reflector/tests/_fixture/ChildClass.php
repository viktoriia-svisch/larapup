<?php
declare(strict_types=1);
namespace SebastianBergmann\ObjectReflector\TestFixture;
class ChildClass extends ParentClass
{
    private $privateInChild = 'private';
    private $protectedInChild = 'protected';
    private $publicInChild = 'public';
    public function __construct()
    {
        $this->undeclared = 'undeclared';
    }
}
