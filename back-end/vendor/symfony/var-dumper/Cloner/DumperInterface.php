<?php
namespace Symfony\Component\VarDumper\Cloner;
interface DumperInterface
{
    public function dumpScalar(Cursor $cursor, $type, $value);
    public function dumpString(Cursor $cursor, $str, $bin, $cut);
    public function enterHash(Cursor $cursor, $type, $class, $hasChild);
    public function leaveHash(Cursor $cursor, $type, $class, $hasChild, $cut);
}
