<?php declare(strict_types=1);
namespace PhpParser;
interface ErrorHandler
{
    public function handleError(Error $error);
}
