<?php
namespace Nexmo\Client\Factory;
interface FactoryInterface
{
    public function hasApi($api);
    public function getApi($api);
}
