<?php
namespace Hamcrest;
interface SelfDescribing
{
    public function describeTo(Description $description);
}