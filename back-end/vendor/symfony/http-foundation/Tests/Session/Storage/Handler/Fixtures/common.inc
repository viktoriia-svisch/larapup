<?php
use Symfony\Component\HttpFoundation\Session\Storage\Handler\AbstractSessionHandler;
$parent = __DIR__;
while (!@file_exists($parent.'/vendor/autoload.php')) 
    if ($parent === dirname($parent)) 
    $parent = dirname($parent);
}
require $parent.'/vendor/autoload.php';
error_reporting(-1);
ini_set('html_errors', 0);
ini_set('display_errors', 1);
ini_set('session.gc_probability', 0);
ini_set('session.serialize_handler', 'php');
ini_set('session.cookie_lifetime', 0);
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', '');
ini_set('session.cookie_httponly', '');
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cache_expire', 180);
ini_set('session.cookie_path', '/');
ini_set('session.cookie_domain', '');
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.lazy_write', 1);
ini_set('session.name', 'sid');
ini_set('session.save_path', __DIR__);
ini_set('session.cache_limiter', '');
header_remove('X-Powered-By');
header('Content-Type: text/plain; charset=utf-8');
register_shutdown_function(function () 
);
ob_start();
class TestSessionHandler extends AbstractSessionHandler
    public function open($path, $name)
    public function validateId($sessionId)
    /**
     * 
     */
    public function read($sessionId)
    /**
     * 
     */
    public function updateTimestamp($sessionId, $data)
    /**
     * 
     */
    public function write($sessionId, $data)
    /**
     * 
     */
    public function destroy($sessionId)
    public function close()
    public function gc($maxLifetime)
    protected function doRead($sessionId)
    protected function doWrite($sessionId, $data)
    protected function doDestroy($sessionId)
}
