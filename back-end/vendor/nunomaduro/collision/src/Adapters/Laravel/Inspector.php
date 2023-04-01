<?php
namespace NunoMaduro\Collision\Adapters\Laravel;
use Whoops\Exception\Inspector as BaseInspector;
class Inspector extends BaseInspector
{
    protected function getTrace($e)
    {
        return $e->getTrace();
    }
}
