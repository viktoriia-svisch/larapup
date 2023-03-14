<?php
namespace Illuminate\Contracts\Auth;
interface SupportsBasicAuth
{
    public function basic($field = 'email', $extraConditions = []);
    public function onceBasic($field = 'email', $extraConditions = []);
}
