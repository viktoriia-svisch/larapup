<?php
namespace NunoMaduro\Collision\Contracts;
interface ArgumentFormatter
{
    public function format(array $arguments, bool $recursive = true): string;
}
