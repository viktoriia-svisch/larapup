<?php
namespace Psy\TabCompletion\Matcher;
use InvalidArgumentException;
class ObjectMethodsMatcher extends AbstractContextAwareMatcher
{
    public function getMatches(array $tokens, array $info = [])
    {
        $input = $this->getInput($tokens);
        $firstToken = \array_pop($tokens);
        if (self::tokenIs($firstToken, self::T_STRING)) {
            \array_pop($tokens);
        }
        $objectToken = \array_pop($tokens);
        if (!\is_array($objectToken)) {
            return [];
        }
        $objectName = \str_replace('$', '', $objectToken[1]);
        try {
            $object = $this->getVariable($objectName);
        } catch (InvalidArgumentException $e) {
            return [];
        }
        if (!\is_object($object)) {
            return [];
        }
        return \array_filter(
            \get_class_methods($object),
            function ($var) use ($input) {
                return AbstractMatcher::startsWith($input, $var) &&
                    !AbstractMatcher::startsWith('__', $var);
            }
        );
    }
    public function hasMatched(array $tokens)
    {
        $token     = \array_pop($tokens);
        $prevToken = \array_pop($tokens);
        switch (true) {
            case self::tokenIs($token, self::T_OBJECT_OPERATOR):
            case self::tokenIs($prevToken, self::T_OBJECT_OPERATOR):
                return true;
        }
        return false;
    }
}
