<?php
namespace Symfony\Component\VarDumper\Exception;
class ThrowingCasterException extends \Exception
{
    public function __construct(\Exception $prev)
    {
        parent::__construct('Unexpected '.\get_class($prev).' thrown from a caster: '.$prev->getMessage(), 0, $prev);
    }
}
