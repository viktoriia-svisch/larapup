<?php
use Symfony\Component\VarDumper\VarDumper;
if (!function_exists('dump')) {
    function dump($var, ...$moreVars)
    {
        VarDumper::dump($var);
        foreach ($moreVars as $v) {
            VarDumper::dump($v);
        }
        if (1 < func_num_args()) {
            return func_get_args();
        }
        return $var;
    }
}
if (!function_exists('dd')) {
    function dd(...$vars)
    {
        foreach ($vars as $v) {
            VarDumper::dump($v);
        }
        die(1);
    }
}
