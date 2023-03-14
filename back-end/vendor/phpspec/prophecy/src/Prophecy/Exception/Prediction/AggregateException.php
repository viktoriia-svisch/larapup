<?php
namespace Prophecy\Exception\Prediction;
use Prophecy\Prophecy\ObjectProphecy;
class AggregateException extends \RuntimeException implements PredictionException
{
    private $exceptions = array();
    private $objectProphecy;
    public function append(PredictionException $exception)
    {
        $message = $exception->getMessage();
        $message = strtr($message, array("\n" => "\n  "))."\n";
        $message = empty($this->exceptions) ? $message : "\n" . $message;
        $this->message      = rtrim($this->message.$message);
        $this->exceptions[] = $exception;
    }
    public function getExceptions()
    {
        return $this->exceptions;
    }
    public function setObjectProphecy(ObjectProphecy $objectProphecy)
    {
        $this->objectProphecy = $objectProphecy;
    }
    public function getObjectProphecy()
    {
        return $this->objectProphecy;
    }
}
