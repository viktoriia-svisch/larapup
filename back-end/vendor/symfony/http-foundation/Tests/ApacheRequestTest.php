<?php
namespace Symfony\Component\HttpFoundation\Tests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ApacheRequest;
class ApacheRequestTest extends TestCase
{
    public function testUriMethods($server, $expectedRequestUri, $expectedBaseUrl, $expectedPathInfo)
    {
        $request = new ApacheRequest();
        $request->server->replace($server);
        $this->assertEquals($expectedRequestUri, $request->getRequestUri(), '->getRequestUri() is correct');
        $this->assertEquals($expectedBaseUrl, $request->getBaseUrl(), '->getBaseUrl() is correct');
        $this->assertEquals($expectedPathInfo, $request->getPathInfo(), '->getPathInfo() is correct');
    }
    public function provideServerVars()
    {
        return [
            [
                [
                    'REQUEST_URI' => '/foo/app_dev.php/bar',
                    'SCRIPT_NAME' => '/foo/app_dev.php',
                    'PATH_INFO' => '/bar',
                ],
                '/foo/app_dev.php/bar',
                '/foo/app_dev.php',
                '/bar',
            ],
            [
                [
                    'REQUEST_URI' => '/foo/bar',
                    'SCRIPT_NAME' => '/foo/app_dev.php',
                ],
                '/foo/bar',
                '/foo',
                '/bar',
            ],
            [
                [
                    'REQUEST_URI' => '/app_dev.php/foo/bar',
                    'SCRIPT_NAME' => '/app_dev.php',
                    'PATH_INFO' => '/foo/bar',
                ],
                '/app_dev.php/foo/bar',
                '/app_dev.php',
                '/foo/bar',
            ],
            [
                [
                    'REQUEST_URI' => '/foo/bar',
                    'SCRIPT_NAME' => '/app_dev.php',
                ],
                '/foo/bar',
                '',
                '/foo/bar',
            ],
            [
                [
                    'REQUEST_URI' => '/app_dev.php',
                    'SCRIPT_NAME' => '/app_dev.php',
                ],
                '/app_dev.php',
                '/app_dev.php',
                '/',
            ],
            [
                [
                    'REQUEST_URI' => '/',
                    'SCRIPT_NAME' => '/app_dev.php',
                ],
                '/',
                '',
                '/',
            ],
        ];
    }
}
