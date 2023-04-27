<?php
namespace Symfony\Component\Routing\Matcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RequestContextAwareInterface;
interface UrlMatcherInterface extends RequestContextAwareInterface
{
    public function match($pathinfo);
}
