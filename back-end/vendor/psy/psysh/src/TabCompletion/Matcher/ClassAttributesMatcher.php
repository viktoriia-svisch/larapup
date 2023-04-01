<?php
namespace Psy\TabCompletion\Matcher;
class ClassAttributesMatcher extends AbstractMatcher
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
        $vars = \array_merge(
            \array_map(
                function ($var) {
                    return '$' . $var;
                },
                \array_keys($reflection->getStaticProperties())
            ),
            \array_keys($reflection->getConstants())
        );
        return \array_map(
            function ($name) use ($class) {
                $chunks = \explode('\\', $class);
                $className = \array_pop($chunks);
                return $className . '::' . $name;
            },
            \array_filter(
                $vars,
                function ($var) use ($input) {
                    return AbstractMatcher::startsWith($input, $var);
                }
            )
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
