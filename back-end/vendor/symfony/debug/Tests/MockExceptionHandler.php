<?php
namespace Symfony\Component\Debug\Tests;
use Symfony\Component\Debug\ExceptionHandler;
class MockExceptionHandler extends ExceptionHandler
{
    public $e;
    public function handle(\Exception $e)
    {
        $this->e = $e;
    }
}
