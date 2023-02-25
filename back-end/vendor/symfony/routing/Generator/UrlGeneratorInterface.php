<?php
namespace Symfony\Component\Routing\Generator;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContextAwareInterface;
interface UrlGeneratorInterface extends RequestContextAwareInterface
{
    const ABSOLUTE_URL = 0;
    const ABSOLUTE_PATH = 1;
    const RELATIVE_PATH = 2;
    const NETWORK_PATH = 3;
    public function generate($name, $parameters = [], $referenceType = self::ABSOLUTE_PATH);
}
