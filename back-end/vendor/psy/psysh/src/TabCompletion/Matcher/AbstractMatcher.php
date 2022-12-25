<?php
namespace Psy\TabCompletion\Matcher;
abstract class AbstractMatcher
{
    const CONSTANT_SYNTAX = '^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$';
    const VAR_SYNTAX = '^\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$';
    const MISC_OPERATORS = '+-*/^|&';
    const T_OPEN_TAG = 'T_OPEN_TAG';
    const T_VARIABLE = 'T_VARIABLE';
    const T_OBJECT_OPERATOR = 'T_OBJECT_OPERATOR';
    const T_DOUBLE_COLON = 'T_DOUBLE_COLON';
    const T_NEW = 'T_NEW';
    const T_CLONE = 'T_CLONE';
    const T_NS_SEPARATOR = 'T_NS_SEPARATOR';
    const T_STRING = 'T_STRING';
    const T_WHITESPACE = 'T_WHITESPACE';
    const T_AND_EQUAL = 'T_AND_EQUAL';
    const T_BOOLEAN_AND = 'T_BOOLEAN_AND';
    const T_BOOLEAN_OR = 'T_BOOLEAN_OR';
    const T_ENCAPSED_AND_WHITESPACE = 'T_ENCAPSED_AND_WHITESPACE';
    const T_REQUIRE = 'T_REQUIRE';
    const T_REQUIRE_ONCE = 'T_REQUIRE_ONCE';
    const T_INCLUDE = 'T_INCLUDE';
    const T_INCLUDE_ONCE = 'T_INCLUDE_ONCE';
    public function hasMatched(array $tokens)
    {
        return false;
    }
    protected function getInput(array $tokens)
    {
        $var = '';
        $firstToken = \array_pop($tokens);
        if (self::tokenIs($firstToken, self::T_STRING)) {
            $var = $firstToken[1];
        }
        return $var;
    }
    protected function getNamespaceAndClass($tokens)
    {
        $class = '';
        while (self::hasToken(
            [self::T_NS_SEPARATOR, self::T_STRING],
            $token = \array_pop($tokens)
        )) {
            if (self::needCompleteClass($token)) {
                continue;
            }
            $class = $token[1] . $class;
        }
        return $class;
    }
    abstract public function getMatches(array $tokens, array $info = []);
    public static function startsWith($prefix, $word)
    {
        return \preg_match(\sprintf('#^%s#', $prefix), $word);
    }
    public static function hasSyntax($token, $syntax = self::VAR_SYNTAX)
    {
        if (!\is_array($token)) {
            return false;
        }
        $regexp = \sprintf('#%s#', $syntax);
        return (bool) \preg_match($regexp, $token[1]);
    }
    public static function tokenIs($token, $which)
    {
        if (!\is_array($token)) {
            return false;
        }
        return \token_name($token[0]) === $which;
    }
    public static function isOperator($token)
    {
        if (!\is_string($token)) {
            return false;
        }
        return \strpos(self::MISC_OPERATORS, $token) !== false;
    }
    public static function needCompleteClass($token)
    {
        return \in_array($token[1], ['doc', 'ls', 'show']);
    }
    public static function hasToken(array $coll, $token)
    {
        if (!\is_array($token)) {
            return false;
        }
        return \in_array(\token_name($token[0]), $coll);
    }
}
