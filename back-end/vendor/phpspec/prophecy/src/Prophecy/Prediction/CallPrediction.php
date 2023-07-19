<?php
namespace Prophecy\Prediction;
use Prophecy\Call\Call;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\MethodProphecy;
use Prophecy\Argument\ArgumentsWildcard;
use Prophecy\Argument\Token\AnyValuesToken;
use Prophecy\Util\StringUtil;
use Prophecy\Exception\Prediction\NoCallsException;
class CallPrediction implements PredictionInterface
{
    private $util;
    public function __construct(StringUtil $util = null)
    {
        $this->util = $util ?: new StringUtil;
    }
    public function check(array $calls, ObjectProphecy $object, MethodProphecy $method)
    {
        if (count($calls)) {
            return;
        }
        $methodCalls = $object->findProphecyMethodCalls(
            $method->getMethodName(),
            new ArgumentsWildcard(array(new AnyValuesToken))
        );
        if (count($methodCalls)) {
            throw new NoCallsException(sprintf(
                "No calls have been made that match:\n".
                "  %s->%s(%s)\n".
                "but expected at least one.\n".
                "Recorded `%s(...)` calls:\n%s",
                get_class($object->reveal()),
                $method->getMethodName(),
                $method->getArgumentsWildcard(),
                $method->getMethodName(),
                $this->util->stringifyCalls($methodCalls)
            ), $method);
        }
        throw new NoCallsException(sprintf(
            "No calls have been made that match:\n".
            "  %s->%s(%s)\n".
            "but expected at least one.",
            get_class($object->reveal()),
            $method->getMethodName(),
            $method->getArgumentsWildcard()
        ), $method);
    }
}