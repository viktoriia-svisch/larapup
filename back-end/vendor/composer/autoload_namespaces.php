<?php
$vendorDir = dirname(dirname(__FILE__));
$baseDir = dirname($vendorDir);
return array(
    'Prophecy\\' => array($vendorDir . '/phpspec/prophecy/src'),
    'Parsedown' => array($vendorDir . '/erusev/parsedown'),
    'Mockery' => array($vendorDir . '/mockery/mockery/library'),
    'Doctrine\\Common\\Lexer\\' => array($vendorDir . '/doctrine/lexer/lib'),
    'Barryvdh' => array($vendorDir . '/barryvdh/reflection-docblock/src'),
);
