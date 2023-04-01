<?php
namespace Psy\TabCompletion\Matcher;
class ClassMethodsMatcher extends AbstractMatcher
{
    public function getMatches(array $tokens, array $info = [])
    {
        $input = $this->getInput($tokens);
        $firstToken = \array_pop($tokens);
        if (self::tokenIs($firstToken, self::T_STRING)) {
            \array_pop($tokens);
        }
        $class = $this->getNamespaceAndClass($tokens);
        try {
            $reflection = new \ReflectionClass($class);
        } catch (\ReflectionException $re) {
            return [];
        }
        if (self::needCompleteClass($tokens[1])) {
            $methods = $reflection->getMethods();
        } else {
            $methods = $reflection->getMethods(\ReflectionMethod::IS_STATIC);
        }
        $methods = \array_map(function (\ReflectionMethod $method) {
            return $method->getName();
        }, $methods);
        return \array_map(
            function ($name) use ($class) {
                $chunks = \explode('\\', $class);
                $className = \array_pop($chunks);
                return $className . '::' . $name;
            },
            \array_filter($methods, function ($method) use ($input) {
                return AbstractMatcher::startsWith($input, $method);
            })
        );
    }
    public function hasMatched(array $tokens)
    {
        $token     = \array_pop($tokens);
        $prevToken = \array_pop($tokens);
        switch (true) {
            case self::tokenIs($prevToken, self::T_DOUBLE_COLON) && self::tokenIs($token, self::T_STRING):
            case self::tokenIs($token, self::T_DOUBLE_COLON):
                return true;
        }
        return false;
    }
}
