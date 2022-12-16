<?php
error_reporting(E_ALL);
function isAbsolutePath($path)
{
    $windowsPattern = '~^[A-Z]:[\\/]~i';
    return ($path[0] === DIRECTORY_SEPARATOR) || (preg_match($windowsPattern, $path) === 1);
}
$root    = realpath(dirname(dirname(__FILE__)));
$composerVendorDirectory = getenv("COMPOSER_VENDOR_DIR") ?: "vendor";
if (!isAbsolutePath($composerVendorDirectory)) {
    $composerVendorDirectory = $root . DIRECTORY_SEPARATOR . $composerVendorDirectory;
}
$autoloadPath = $composerVendorDirectory . DIRECTORY_SEPARATOR . 'autoload.php';
if (!file_exists($autoloadPath)) {
    throw new Exception(
        'Please run "php composer.phar install" in root directory '
        . 'to setup unit test dependencies before running the tests'
    );
}
require_once $autoloadPath;
$hamcrestRelativePath = 'hamcrest/hamcrest-php/hamcrest/Hamcrest.php';
if (DIRECTORY_SEPARATOR !== '/') {
    $hamcrestRelativePath = str_replace('/', DIRECTORY_SEPARATOR, $hamcrestRelativePath);
}
$hamcrestPath = $composerVendorDirectory . DIRECTORY_SEPARATOR . $hamcrestRelativePath;
require_once $hamcrestPath;
Mockery::globalHelpers();
unset($root, $autoloadPath, $hamcrestPath, $composerVendorDirectory);
