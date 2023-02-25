<?php
namespace Tymon\JWTAuth\Contracts\Providers;
interface Storage
{
    public function add($key, $value, $minutes);
    public function forever($key, $value);
    public function get($key);
    public function destroy($key);
    public function flush();
}
