<?php
namespace Symfony\Component\Routing\Matcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\NoConfigurationException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
interface RequestMatcherInterface
{
    public function matchRequest(Request $request);
}
