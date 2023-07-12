#!/usr/bin/env php
<?php declare(strict_types=1);
$functions         = require __DIR__ . '/FunctionSignatureMap.php';
$resourceFunctions = [];
foreach ($functions as $function => $arguments) {
    foreach ($arguments as $argument) {
        if (strpos($argument, '?') === 0) {
            $argument = substr($argument, 1);
        }
        if ($argument === 'resource') {
            $resourceFunctions[] = explode('\'', $function)[0];
        }
    }
}
$resourceFunctions = array_unique($resourceFunctions);
sort($resourceFunctions);
$buffer = <<<EOT
<?php declare(strict_types=1);
namespace SebastianBergmann\ResourceOperations;
final class ResourceOperations
{
    public static function getFunctions(): array
    {
        return [
EOT;
foreach ($resourceFunctions as $function) {
    $buffer .= sprintf("            '%s',\n", $function);
}
$buffer .= <<< EOT
        ];
    }
}
EOT;
file_put_contents(__DIR__ . '/../src/ResourceOperations.php', $buffer);
