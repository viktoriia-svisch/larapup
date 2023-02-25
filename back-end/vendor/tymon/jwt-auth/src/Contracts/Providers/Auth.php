<?php
namespace Tymon\JWTAuth\Contracts\Providers;
interface Auth
{
    public function byCredentials(array $credentials);
    public function byId($id);
    public function user();
}
