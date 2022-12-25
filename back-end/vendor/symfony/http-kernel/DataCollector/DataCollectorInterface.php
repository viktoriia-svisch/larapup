<?php
namespace Symfony\Component\HttpKernel\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\ResetInterface;
interface DataCollectorInterface extends ResetInterface
{
    public function collect(Request $request, Response $response, \Exception $exception = null);
    public function getName();
}
