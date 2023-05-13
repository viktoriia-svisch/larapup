<?php
namespace Psy\TabCompletion\Matcher;
class ConstantsMatcher extends AbstractMatcher
{
    public function getMatches(array $tokens, array $info = [])
    {
        $const = $this->getInput($tokens);
        return \array_filter(\array_keys(\get_defined_constants()), function ($constant) use ($const) {
            return AbstractMatcher::startsWith($const, $constant);
        });
    }
    public function hasMatched(array $tokens)
    {
        $token     = \array_pop($tokens);
        $prevToken = \array_pop($tokens);
        switch (true) {
            case self::tokenIs($prevToken, self::T_NEW):
            case self::tokenIs($prevToken, self::T_NS_SEPARATOR):
                return false;
            case self::hasToken([self::T_OPEN_TAG, self::T_STRING], $token):
            case self::isOperator($token):
                return true;
        }
        return false;
    }
}
