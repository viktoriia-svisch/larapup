<?php declare(strict_types=1);
namespace PhpParser;
interface Parser
{
    public function parse(string $code, ErrorHandler $errorHandler = null);
}
