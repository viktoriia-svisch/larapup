<?php
namespace App\Providers;
class AccessTokenGuard
{
    public function __construct(\Illuminate\Foundation\Application $userProvider, \Illuminate\Foundation\Application $request, array $config)
    {
    }
}
