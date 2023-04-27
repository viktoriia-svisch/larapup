<?php
namespace Opis\Closure;
interface ISecurityProvider
{
    public function sign($closure);
    public function verify(array $data);
}
