<?php
namespace Hamcrest;
interface Matcher extends SelfDescribing
{
    public function matches($item);
    public function describeMismatch($item, Description $description);
}
