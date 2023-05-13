<?php
namespace Opis\Closure;
class ClosureContext
{
    public $scope;
    public $locks;
    public function __construct()
    {
        $this->scope = new ClosureScope();
        $this->locks = 0;
    }
}
