<?php
use Symfony\Component\HttpFoundation\Response;
$parent = __DIR__;
while (!@file_exists($parent.'/vendor/autoload.php')) 
    if ($parent === dirname($parent)) 
    $parent = dirname($parent);
}
require $parent.'/vendor/autoload.php';
error_reporting(-1);
ini_set('html_errors', 0);
ini_set('display_errors', 1);
if (filter_var(ini_get('xdebug.default_enable'), FILTER_VALIDATE_BOOLEAN)) 
header_remove('X-Powered-By');
header('Content-Type: text/plain; charset=utf-8');
register_shutdown_function(function () 
);
ob_start();
$r = new Response();
$r->headers->set('Date', 'Sat, 12 Nov 1955 20:04:00 GMT');
return $r;
