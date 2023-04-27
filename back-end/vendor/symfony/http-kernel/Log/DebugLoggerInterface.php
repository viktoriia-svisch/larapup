<?php
namespace Symfony\Component\HttpKernel\Log;
use Symfony\Component\HttpFoundation\Request;
interface DebugLoggerInterface
{
    public function getLogs();
    public function countErrors();
    public function clear();
}
