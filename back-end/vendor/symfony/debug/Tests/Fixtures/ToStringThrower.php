<?php
namespace Symfony\Component\Debug\Tests\Fixtures;
class ToStringThrower
{
    private $exception;
    public function __construct(\Exception $e)
    {
        $this->exception = $e;
    }
    public function __toString()
    {
        try {
            throw $this->exception;
        } catch (\Exception $e) {
            return trigger_error($e, E_USER_ERROR);
        }
    }
}
