<?php
namespace NunoMaduro\Collision\Contracts;
interface Highlighter
{
    public function highlight(string $content, int $line): string;
}
