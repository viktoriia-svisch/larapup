<?php
interface iTemplate
{
    public function setVariable($name, $var);
    public function
        getHtml($template);
}
interface a
{
    public function foo();
}
interface b extends a
{
    public function baz(Baz $baz);
}
class c implements b
{
    public function foo()
    {
    }
    public function baz(Baz $baz)
    {
    }
}
