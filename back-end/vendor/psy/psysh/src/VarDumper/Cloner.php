<?php
namespace Psy\VarDumper;
use Symfony\Component\VarDumper\Caster\Caster;
use Symfony\Component\VarDumper\Cloner\Stub;
use Symfony\Component\VarDumper\Cloner\VarCloner;
class Cloner extends VarCloner
{
    private $filter = 0;
    public function cloneVar($var, $filter = 0)
    {
        $this->filter = $filter;
        return parent::cloneVar($var, $filter);
    }
    protected function castResource(Stub $stub, $isNested)
    {
        return Caster::EXCLUDE_VERBOSE & $this->filter ? [] : parent::castResource($stub, $isNested);
    }
}
