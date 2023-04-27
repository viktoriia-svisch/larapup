<?php
namespace NunoMaduro\Collision\Contracts\Adapters\Phpunit;
use PHPUnit\Framework\TestListener;
interface Listener extends TestListener
{
    public function render(\Throwable $t);
}
