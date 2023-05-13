<?php
namespace NunoMaduro\Collision\Contracts;
interface Provider
{
    public function register(): Provider;
    public function getHandler(): Handler;
}
