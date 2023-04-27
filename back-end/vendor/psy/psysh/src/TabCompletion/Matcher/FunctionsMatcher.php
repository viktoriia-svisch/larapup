<?php
namespace Psy\TabCompletion\Matcher;
class FunctionsMatcher extends AbstractMatcher
{
    public function getMatches(array $tokens, array $info = [])
    {
        $func = $this->getInput($tokens);
        $functions    = \get_defined_functions();
        $allFunctions = \array_merge($functions['user'], $functions['internal']);
        return \array_filter($allFunctions, function ($function) use ($func) {
            return AbstractMatcher::startsWith($func, $function);
        });
    }
    public function hasMatched(array $tokens)
    {
        $token     = \array_pop($tokens);
        $prevToken = \array_pop($tokens);
        switch (true) {
            case self::tokenIs($prevToken, self::T_NEW):
                return false;
            case self::hasToken([self::T_OPEN_TAG, self::T_STRING], $token):
            case self::isOperator($token):
                return true;
        }
        return false;
    }
}
